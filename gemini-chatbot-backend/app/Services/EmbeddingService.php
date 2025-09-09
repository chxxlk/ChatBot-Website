<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class EmbeddingService
{
    private $apiKey;

    public function __construct()
    {
        $this->apiKey = env('GEMINI_API_KEY');
    }

    /**
     * Mendapatkan embedding dari teks dengan menggunakan API Gemini.
     *
     * @param string $text Teks yang akan di-embed
     * @return array|null Nilai embedding dalam bentuk array. Jika gagal, maka akan
     *                    mengembalikan nilai null.
     */
    public function getEmbedding($text)
    {
        try {
            $response = Http::timeout(30)
                ->post('https://generativelanguage.googleapis.com/v1/models/embedding-001:embedContent?key=' . $this->apiKey, [
                    'content' => [
                        'parts' => [
                            ['text' => $text]
                        ]
                    ]
                ]);

            if ($response->failed()) {
                throw new \Exception('Gagal mendapatkan embedding');
            }

            $data = $response->json();
            return $data['embedding']['values'] ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Menghitung nilai kemiripan cosinus (cosine similarity) antara dua vektor.
     *
     * @param array $vec1 Vektor pertama
     * @param array $vec2 Vektor kedua
     * @return float Nilai kemiripan cosinus
     */
    public function cosineSimilarity($vec1, $vec2)
    {
        if (count($vec1) !== count($vec2)) {
            return 0;
        }

        $dotProduct = 0;
        $norm1 = 0;
        $norm2 = 0;

        for ($i = 0; $i < count($vec1); $i++) {
            $dotProduct += $vec1[$i] * $vec2[$i];
            $norm1 += $vec1[$i] * $vec1[$i];
            $norm2 += $vec2[$i] * $vec2[$i];
        }

        $norm1 = sqrt($norm1);
        $norm2 = sqrt($norm2);

        if ($norm1 == 0 || $norm2 == 0) {
            return 0;
        }

        return $dotProduct / ($norm1 * $norm2);
    }

    /**
     * Mencari data yang mirip dengan query yang diberikan dengan menggunakan
     * cosine similarity.
     *
     * @param string $query Query yang akan dicari
     * @param string $table Nama tabel yang akan di-cari
     * @param string $textColumn Nama kolom yang berisi teks yang akan di-cari
     * @param int|null $limit Jumlah data yang akan di-ambil
     * @return \Illuminate\Support\Collection|null Data yang mirip dengan query.
     *                                            Jika tidak ada data yang mirip,
     *                                            maka akan mengembalikan nilai null.
     */
    public function findSimilarData($query, $table, $textColumn, $limit = null)
    {
        $queryEmbedding = $this->getEmbedding($query);
        if (!$queryEmbedding) {
            return DB::table($table)->limit($limit)->get();
        }

        $allData = DB::table($table)->get();
        $scoredData = [];

        foreach ($allData as $item) {
            $itemEmbedding = $this->getEmbedding($item->$textColumn);
            if ($itemEmbedding) {
                $similarity = $this->cosineSimilarity($queryEmbedding, $itemEmbedding);
                $scoredData[] = [
                    'data' => $item,
                    'score' => $similarity
                ];
            }
        }

        // Urutkan berdasarkan similarity score
        usort($scoredData, function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        // Ambil data terbaik
        return array_slice(array_column($scoredData, 'data'), 0, $limit);
    }
}
