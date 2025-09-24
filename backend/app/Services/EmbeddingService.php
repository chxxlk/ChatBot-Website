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
        $this->apiKey = env('HF_API_KEY');
        $this->baseUrl = env('HF_API_BASE');
        $this->model = env('HF_MODEL');

        if (!isset($this->apiKey) || !isset($this->baseUrl) || !isset($this->model)) {
            throw new \Exception('Missing HuggingFace API key, base URL, or model name.');
        }
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

            $url = "{$this->baseUrl}/nebius/v1/embeddings";
            Log::info('Request ke HuggingFace', ['url' => $url]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(180)
                ->post($url, [
                    'model' => $this->model,
                    'input' => $text,
                ]);


            Log::info('Response HF', [
                'status' => $response->status(),
                'body'   => $response->body()
            ]);

            if ($response->successful()) {
                $data = $response->json();

                // Log::info('HF Raw embedding response: ', $data);
                if (isset($data['data'][0]['embedding'])) {
                    $emb = $data['data'][0]['embedding'];

                    // Jika embedding adalah objek dengan key "0", "1", ... ubah ke array numeric
                    if (is_array($emb)) {
                        // Jika associative (misalnya key "0", "1", "2", ...) atau numeric
                        $vector = array_values($emb);
                        $dimensi = count($vector);

                        Log::info('Embedding berhasil', ['dimensi' => $dimensi]);

                        return $vector;
                    }
                }
                Log::warning('Embedding response tidak sesuai', ['response' => $data]);
                return $data['embedding']['values'] ?? null;
            }
            Log::error('HF Embedding failed', [
                'status' => $response->status(),
                'body'   => $response->body()
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error('HF Embedding error: ' . $e->getMessage());
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
            if (! $vector) Log::warning('Embedding gagal untuk text', ['text' => $text]);
        }

        Log::info('Generated batch embeddings', ['embeddings' => $embeddings]);

        return $embeddings;
    }

    /**
     * Semantic search sederhana (per item generate embedding)
     */
    public function semanticSearch(string $query, string $table, $limit = 5, $threshold = 0.3)
    {
        // try {
        //     $queryEmbedding = $this->generateEmbedding($query);
        //     if (!$queryEmbedding) {
        //         Log::warning('Failed to generate query embedding, fallback ke simple search');
        //         return DB::table($table)->limit($limit)->get();
        //     }

        //     $allData = DB::table($table)->get();
        //     $scoredResults = [];

        //     foreach ($allData as $item) {
        //         $combinedText = '';
        //         foreach ($textColumns as $column) {
        //             if (isset($item->$column)) {
        //                 $combinedText .= $item->$column . ' ';
        //             }
        //         }

        //         if (!empty(trim($combinedText))) {
        //             $itemEmbedding = $this->generateEmbedding($combinedText);

        //             if ($itemEmbedding) {
        //                 $similarity = $this->cosineSimilarity($queryEmbedding, $itemEmbedding);

        //                 if ($similarity >= $threshold) {
        //                     $scoredResults[] = [
        //                         'item' => $item,
        //                         'score' => $similarity,
        //                     ];
        //                 }
        //             }
        //         }
        //     }

        //     usort($scoredResults, fn($a, $b) => $b['score'] <=> $a['score']);

        //     return array_slice(array_map(fn($r) => $r['item'], $scoredResults), 0, $limit);
        // } catch (\Exception $e) {
        //     Log::error('Semantic search error: ' . $e->getMessage());
        //     return DB::table($table)->limit($limit)->get();
        // }
        // 1. Buat embedding query
        $queryVector = $this->generateEmbedding($query);
        
        if (!$queryVector) {
            Log::warning('❌ Gagal membuat embedding query');
            return collect();
        }

        // Ambil hasil + jarak cosine langsung dari Postgres
        $results = DB::table('embeddings')
            ->select('row_id', DB::raw("vector <=> '" . '[' . implode(',', $queryVector) . ']' . "' as distance"))
            ->where('table_name', $table)
            ->orderBy('distance')
            ->limit($limit)
            ->get();

        return $results->map(function ($row) use ($table) {
            $original = DB::table($table)->find($row->row_id);
            return [
                'similarity' => 1 - (float)$row->distance, // 1 = mirip banget, 0 = tidak mirip
                'data'       => $original,
            ];
        });
    }
}
