<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class EmbeddingService
{
    private $apiKey;
    private $baseUrl;
    private $model;

    public function __construct()
    {
        $this->apiKey = env('OPENROUTER_API_KEY');
        $this->baseUrl = env('OPENROUTER_BASE_URL', 'https://openrouter.ai/api/v1');
        $this->model = env('EMBEDDING_MODEL', 'text-embedding-ada-002'); // Default model
    }

    /**
     * Generate embeddings untuk teks menggunakan OpenRouter
     */
    public function generateEmbedding($text)
    {
        try {
            if (empty($text)) {
                return null;
            }

            // Clean text untuk embedding
            $cleanText = $this->preprocessText($text);

            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                    'HTTP-Referer' => env('APP_URL', 'http://localhost:8000'),
                    'X-Title' => 'Fernando Chatbot - Embedding Service'
                ])
                ->post($this->baseUrl . '/embeddings', [
                    'model' => $this->model,
                    'input' => $cleanText,
                    'encoding_format' => 'float'
                ]);

            if ($response->failed()) {
                Log::error('Embedding API Error: ' . $response->body());
                throw new \Exception('Gagal generate embedding: ' . $response->status());
            }

            $data = $response->json();

            if (isset($data['data'][0]['embedding'])) {
                return $data['data'][0]['embedding'];
            } else {
                Log::error('Embedding response format: ' . json_encode($data));
                throw new \Exception('Format respons embedding tidak sesuai');
            }
        } catch (\Exception $e) {
            Log::error('Embedding Service Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Preprocess text untuk embedding
     */
    private function preprocessText($text)
    {
        // Remove special characters, extra spaces, etc.
        $text = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text); // Hapus special chars
        $text = preg_replace('/\s+/', ' ', $text); // Hapus extra spaces
        $text = trim($text);
        $text = mb_substr($text, 0, 8192); // Truncate to model limit

        return $text;
    }

    /**
     * Hitung cosine similarity antara dua vectors
     */
    public function cosineSimilarity($vecA, $vecB)
    {
        if (!$vecA || !$vecB || count($vecA) !== count($vecB)) {
            return 0;
        }

        $dotProduct = 0;
        $normA = 0;
        $normB = 0;

        for ($i = 0; $i < count($vecA); $i++) {
            $dotProduct += $vecA[$i] * $vecB[$i];
            $normA += $vecA[$i] * $vecA[$i];
            $normB += $vecB[$i] * $vecB[$i];
        }

        $normA = sqrt($normA);
        $normB = sqrt($normB);

        if ($normA == 0 || $normB == 0) {
            return 0;
        }

        return $dotProduct / ($normA * $normB);
    }

    /**
     * Semantic search dengan embedding-based similarity
     */
    public function semanticSearch($query, $table, $textColumns, $limit = 5, $similarityThreshold = 0.7)
    {
        try {
            // Generate embedding untuk query
            $queryEmbedding = $this->generateEmbedding($query);

            if (!$queryEmbedding) {
                // Fallback ke traditional search
                return DB::table($table)
                    ->where(function ($q) use ($textColumns, $query) {
                        foreach ($textColumns as $column) {
                            $q->orWhere($column, 'LIKE', "%{$query}%");
                        }
                    })
                    ->limit($limit)
                    ->get();
            }

            // Ambil semua data dan hitung similarity
            $allData = DB::table($table)->get();
            $scoredResults = [];

            foreach ($allData as $item) {
                // Gabungkan text dari semua columns
                $combinedText = '';
                foreach ($textColumns as $column) {
                    if (isset($item->$column)) {
                        $combinedText .= $item->$column . ' ';
                    }
                }

                if (!empty(trim($combinedText))) {
                    // Generate atau get cached embedding untuk item
                    $itemEmbedding = $this->getCachedEmbedding($table, $item->id, $combinedText);

                    if ($itemEmbedding) {
                        $similarity = $this->cosineSimilarity($queryEmbedding, $itemEmbedding);

                        if ($similarity >= $similarityThreshold) {
                            $scoredResults[] = [
                                'item' => $item,
                                'score' => $similarity,
                                'embedding' => $itemEmbedding
                            ];
                        }
                    }
                }
            }

            // Urutkan berdasarkan similarity score
            usort($scoredResults, function ($a, $b) {
                return $b['score'] <=> $a['score'];
            });

            // Ambil top results
            $topResults = array_slice($scoredResults, 0, $limit);

            return array_map(function ($result) {
                return $result['item'];
            }, $topResults);
        } catch (\Exception $e) {
            Log::error('Semantic search error: ' . $e->getMessage());

            // Fallback ke traditional search
            return DB::table($table)
                ->where(function ($q) use ($textColumns, $query) {
                    foreach ($textColumns as $column) {
                        $q->orWhere($column, 'LIKE', "%{$query}%");
                    }
                })
                ->limit($limit)
                ->get();
        }
    }

    /**
     * Get cached embedding atau generate baru
     */
    private function getCachedEmbedding($table, $itemId, $text)
    {
        // Cek cache dulu
        $cacheKey = "embedding:{$table}:{$itemId}";
        $cachedEmbedding = cache($cacheKey);

        if ($cachedEmbedding) {
            return $cachedEmbedding;
        }

        // Generate baru dan cache
        $embedding = $this->generateEmbedding($text);

        if ($embedding) {
            // Cache untuk 30 hari
            cache([$cacheKey => $embedding], now()->addDays(30));
        }

        return $embedding;
    }

    /**
     * Batch semantic search untuk multiple tables
     */
    public function multiTableSemanticSearch($query, $tablesConfig, $limitPerTable = 3)
    {
        $results = [];

        foreach ($tablesConfig as $tableName => $config) {
            $textColumns = $config['columns'];
            $results[$tableName] = $this->semanticSearch(
                $query,
                $tableName,
                $textColumns,
                $limitPerTable
            );
        }

        return $results;
    }

    /**
     * Test embedding service
     */
    public function testConnection()
    {
        try {
            $testText = "Hello world embedding test";
            $embedding = $this->generateEmbedding($testText);

            return [
                'success' => $embedding !== null,
                'embedding_length' => $embedding ? count($embedding) : 0,
                'model' => $this->model
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
