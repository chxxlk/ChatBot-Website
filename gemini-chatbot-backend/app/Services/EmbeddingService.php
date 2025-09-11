<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class EmbeddingService
{
    private $apiKey;

    public function __construct()
    {
        $this->apiKey = env('GEMINI_API_KEY');
    }

    /**
     * Generate embeddings untuk teks menggunakan Gemini API
     */
    public function generateEmbedding($text)
    {
        try {
            if (empty($text)) {
                return null;
            }

            $response = Http::timeout(30)
                ->post("https://generativelanguage.googleapis.com/v1beta/models/embedding-001:embedContent?key={$this->apiKey}", [
                    'content' => [
                        'parts' => [
                            ['text' => $text]
                        ]
                    ]
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['embedding']['values'] ?? null;
            }

            Log::error('Embedding generation failed: ' . $response->body());
            return null;

        } catch (\Exception $e) {
            Log::error('Embedding service error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Hitung cosine similarity antara dua vectors
     */
    public function cosineSimilarity($vectorA, $vectorB)
    {
        if (!$vectorA || !$vectorB || count($vectorA) !== count($vectorB)) {
            return 0;
        }

        $dotProduct = 0;
        $normA = 0;
        $normB = 0;

        for ($i = 0; $i < count($vectorA); $i++) {
            $dotProduct += $vectorA[$i] * $vectorB[$i];
            $normA += $vectorA[$i] * $vectorA[$i];
            $normB += $vectorB[$i] * $vectorB[$i];
        }

        $normA = sqrt($normA);
        $normB = sqrt($normB);

        if ($normA == 0 || $normB == 0) {
            return 0;
        }

        return $dotProduct / ($normA * $normB);
    }

    /**
     * Semantic search untuk mencari data yang relevan
     */
    public function semanticSearch($query, $table, $textColumns, $limit = 5, $threshold = 0.5)
    {
        try {
            // Generate embedding untuk query
            $queryEmbedding = $this->generateEmbedding($query);
            if (!$queryEmbedding) {
                return DB::table($table)->limit($limit)->get();
            }

            // Ambil semua data dari tabel
            $allData = DB::table($table)->get();
            $scoredResults = [];

            foreach ($allData as $item) {
                // Gabungkan text dari semua columns yang ditentukan
                $combinedText = '';
                foreach ($textColumns as $column) {
                    if (isset($item->$column)) {
                        $combinedText .= $item->$column . ' ';
                    }
                }

                if (!empty(trim($combinedText))) {
                    // Generate embedding untuk item
                    $itemEmbedding = $this->generateEmbedding($combinedText);
                    
                    if ($itemEmbedding) {
                        $similarity = $this->cosineSimilarity($queryEmbedding, $itemEmbedding);
                        
                        if ($similarity >= $threshold) {
                            $scoredResults[] = [
                                'item' => $item,
                                'score' => $similarity
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
            return DB::table($table)->limit($limit)->get();
        }
    }

    /**
     * Batch semantic search untuk multiple tables
     */
    public function multiTableSemanticSearch($query, $tablesConfig, $limitPerTable = 3)
    {
        $results = [];

        foreach ($tablesConfig as $tableName => $config) {
            $textColumns = $config['columns'];
            $results[$tableName] = $this->semanticSearch($query, $tableName, $textColumns, $limitPerTable);
        }

        return $results;
    }
}