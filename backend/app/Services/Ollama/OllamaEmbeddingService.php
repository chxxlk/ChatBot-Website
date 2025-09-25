<?php

namespace App\Services\Ollama;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class OllamaEmbeddingService
{
    private $baseUrl;
    private $model;

    public function __construct()
    {
        $this->baseUrl = env('OLLAMA_BASE_URL');
        $this->model = env('OLLAMA_EMBEDDING_MODEL');
    }

    /**
     * Generate embedding untuk satu teks
     */
    public function generateEmbedding($text)
    {
        try {
            if (empty(trim($text))) {
                return null;
            }

            $url = "{$this->baseUrl}/api/embeddings";

            Log::info('Request ke Ollama', ['url' => $url]);

            $response = Http::timeout(180)
                ->post($url, [
                    'model' => $this->model,
                    'inputs' => $text,
                ]);


            Log::info('Response Ollama', [
                'status' => $response->status(),
                'body'   => $response->body()
            ]);

            if ($response->successful()) {
                $data = $response->json();

                Log::info('Ollama Raw embedding response: ', $data);
                Log::info('Embedding berhasil', ['dimensi' => count($data[0] ?? [])]);

                // kasus 1: [[...]] (array 2D)
                if (is_array($data) && isset($data[0]) && is_array($data[0])) {
                    return $data[0];
                }

                // kasus 2: {"embedding": [...]} 
                if (isset($data['embedding']) && is_array($data['embedding'])) {
                    return $data['embedding'];
                }

                // fallback: return raw data
                return $data;
            }
            Log::error('Ollama Embedding failed', [
                'status' => $response->status(),
                'body'   => $response->body()
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error('Ollama Embedding error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate embeddings untuk batch teks
     */
    public function generateBatchEmbeddings(array $texts)
    {
        $embeddings = [];

        foreach ($texts as $text) {
            $vector = $this->generateEmbedding($text);
            if ($vector) {
                $embeddings[] = $vector;
            }
        }

        return $embeddings;
    }

    /**
     * Hitung cosine similarity antara dua vector
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
            $normA += $vectorA[$i] ** 2;
            $normB += $vectorB[$i] ** 2;
        }

        $normA = sqrt($normA);
        $normB = sqrt($normB);

        if ($normA == 0 || $normB == 0) {
            return 0;
        }

        return $dotProduct / ($normA * $normB);
    }

    /**
     * Semantic search sederhana (per item generate embedding)
     */
    public function semanticSearch($query, $table, $textColumns, $limit = 5, $threshold = 0.3)
    {
        try {
            $queryEmbedding = $this->generateEmbedding($query);
            if (!$queryEmbedding) {
                Log::warning('Failed to generate query embedding, fallback ke simple search');
                return DB::table($table)->limit($limit)->get();
            }

            $allData = DB::table($table)->get();
            $scoredResults = [];

            foreach ($allData as $item) {
                $combinedText = '';
                foreach ($textColumns as $column) {
                    if (isset($item->$column)) {
                        $combinedText .= $item->$column . ' ';
                    }
                }

                if (!empty(trim($combinedText))) {
                    $itemEmbedding = $this->generateEmbedding($combinedText);

                    if ($itemEmbedding) {
                        $similarity = $this->cosineSimilarity($queryEmbedding, $itemEmbedding);

                        if ($similarity >= $threshold) {
                            $scoredResults[] = [
                                'item' => $item,
                                'score' => $similarity,
                            ];
                        }
                    }
                }
            }

            usort($scoredResults, fn($a, $b) => $b['score'] <=> $a['score']);

            return array_slice(array_map(fn($r) => $r['item'], $scoredResults), 0, $limit);
        } catch (\Exception $e) {
            Log::error('Semantic search error: ' . $e->getMessage());
            return DB::table($table)->limit($limit)->get();
        }
    }

    /**
     * Optimized semantic search (batch generate embeddings)
     */
    public function optimizedSemanticSearch($query, $table, $textColumns, $limit = 5, $threshold = 0.3)
    {
        Log::info('Optimized semantic search start', ['query' => $query]);
        try {
            $queryEmbedding = $this->generateEmbedding($query);
            if (!$queryEmbedding) {
                return DB::table($table)->limit($limit)->get();
            }

            $allData = DB::table($table)->get();
            $texts = [];
            $items = [];

            foreach ($allData as $item) {
                $combinedText = '';
                foreach ($textColumns as $column) {
                    if (isset($item->$column)) {
                        $combinedText .= $item->$column . ' ';
                    }
                }

                if (!empty(trim($combinedText))) {
                    $texts[] = $combinedText;
                    $items[] = $item;
                }
            }

            $embeddings = $this->generateBatchEmbeddings($texts);

            if (count($embeddings) !== count($items)) {
                Log::warning('Batch embedding count mismatch');
                return DB::table($table)->limit($limit)->get();
            }

            $scoredResults = [];
            for ($i = 0; $i < count($items); $i++) {
                $similarity = $this->cosineSimilarity($queryEmbedding, $embeddings[$i]);

                if ($similarity >= $threshold) {
                    $scoredResults[] = [
                        'item' => $items[$i],
                        'score' => $similarity,
                    ];
                }
            }

            usort($scoredResults, fn($a, $b) => $b['score'] <=> $a['score']);
            Log::info('Semantic search selesai', ['scores' => $scoredResults]);

            return array_slice(array_map(fn($r) => $r['item'], $scoredResults), 0, $limit);
        } catch (\Exception $e) {
            Log::error('Optimized semantic search error: ' . $e->getMessage());
            return DB::table($table)->limit($limit)->get();
        }
    }
}
