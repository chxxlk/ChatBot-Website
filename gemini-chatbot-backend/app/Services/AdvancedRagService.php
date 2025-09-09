<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class AdvancedRagService
{
    private $embeddingService;
    private $basicRagService;

    public function __construct()
    {
        $this->embeddingService = new EmbeddingService();
        $this->basicRagService = new RagService();
    }

    /**
     * Fungsi untuk melakukan query cerdas ke database kampus.
     *
     * Fungsi ini akan menentukan apakah query tersebut sederhana atau kompleks.
     * Jika query sederhana, maka digunakan basic RAG.
     * Jika query kompleks, maka digunakan semantic search.
     *
     * @param string $userQuery query yang diinputkan oleh user
     * @return string jawaban yang dihasilkan oleh AI
     */
    public function smartQuery($userQuery)
    {
        // Untuk query sederhana, gunakan basic RAG
        if ($this->isSimpleQuery($userQuery)) {
            return $this->basicRagService->queryWithContext($userQuery);
        }

        // Untuk query kompleks, gunakan semantic search
        return $this->semanticQuery($userQuery);
    }

    private function isSimpleQuery($query)
    {
        $simpleKeywords = [
            'pengumuman',
            'dosen',
            'alumni',
            'lowongan',
            'prodi',
            'himpunan',
            'jadwal',
            'info'
        ];

        $query = strtolower($query);
        foreach ($simpleKeywords as $keyword) {
            if (strpos($query, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }

    private function semanticQuery($userQuery)
    {
        // Cari data relevan dari semua tabel menggunakan semantic search
        $context = $this->getSemanticContext($userQuery);

        $prompt = <<<PROMPT
Berdasarkan informasi database kampus berikut:

{$context}

Pertanyaan: {$userQuery}

Jawablah dengan informatif dan akurat. Jika informasi tidak cukup, katakan dengan jujur.

Jawaban:
PROMPT;

        return $this->basicRagService->generateResponse($prompt);
    }

    private function getSemanticContext($query)
    {
        $context = "";

        // Dapatkan data relevan dari setiap tabel
        $context .= $this->getSemanticData($query, 'pengumuman', 'judul', 'isi');
        $context .= $this->getSemanticData($query, 'dosen', 'nama', 'bidang_keahlian');
        $context .= $this->getSemanticData($query, 'berita_alumni', 'judul', 'isi');
        $context .= $this->getSemanticData($query, 'lowongan_asisten_dosen', 'mata_kuliah', 'deskripsi');
        $context .= $this->getSemanticData($query, 'profil_program_studi', 'nama_program_studi', 'deskripsi');
        $context .= $this->getSemanticData($query, 'profil_himpunan_mahasiswa', 'nama_himpunan', 'deskripsi');

        return $context;
    }

    /**
     * Fungsi untuk mendapatkan data relevan dari suatu tabel dengan menggunakan
     * semantic search.
     *
     * Fungsi ini akan mengembalikan string berisi informasi yang sesuai dengan
     * query yang diinputkan. Jika tidak ada data yang sesuai, maka akan
     * mengembalikan string kosong.
     *
     * Jika semantic search gagal, maka akan digunakan query biasa.
     *
     * @param string $query query yang diinputkan oleh user
     * @param string $table nama tabel yang ingin diquery
     * @param string $titleColumn nama kolom yang berisi judul
     * @param string $contentColumn nama kolom yang berisi isi
     * @return string informasi yang sesuai dengan query
     */
    private function getSemanticData($query, $table, $titleColumn, $contentColumn)
    {
        try {
            $data = $this->embeddingService->findSimilarData($query, $table, $contentColumn, 5);

            if (!empty($data)) {
                $result = strtoupper(str_replace('_', ' ', $table)) . ":\n";
                foreach ($data as $item) {
                    $result .= "• {$item->$titleColumn}: " .
                        substr($item->$contentColumn, 0, 100) . "...\n";
                }
                $result .= "\n";
                return $result;
            }
        } catch (\Exception $e) {
            // Fallback ke query biasa jika semantic search gagal
            return $this->getBasicData($table, $titleColumn, $contentColumn);
        }

        return "";
    }

    /**
     * Fungsi untuk mendapatkan data relevan dari suatu tabel dengan menggunakan
     * query biasa.
     *
     * Fungsi ini akan mengembalikan string berisi informasi yang sesuai dengan
     * query yang diinputkan. Jika tidak ada data yang sesuai, maka akan
     * mengembalikan string kosong.
     *
     * @param string $table nama tabel yang ingin diquery
     * @param string $titleColumn nama kolom yang berisi judul
     * @param string $contentColumn nama kolom yang berisi isi
     * @return string informasi yang sesuai dengan query
     */
    private function getBasicData($table, $titleColumn, $contentColumn)
    {
        $data = DB::table($table)->limit(2)->get();

        if ($data->isNotEmpty()) {
            $result = strtoupper(str_replace('_', ' ', $table)) . ":\n";
            foreach ($data as $item) {
                $result .= "• {$item->$titleColumn}: " .
                    substr($item->$contentColumn, 0, 100) . "...\n";
            }
            $result .= "\n";
            return $result;
        }

        return "";
    }
}
