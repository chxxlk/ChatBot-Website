<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RagService
{
    private $apiKey;

    public function __construct()
    {
        $this->apiKey = env('GEMINI_API_KEY');
    }

    public function queryWithContext($userQuery)
    {
        // 1. Retrieve: Ambil data relevan dari database
        $context = $this->retrieveRelevantData($userQuery);

        // 2. Augment: Gabungkan dengan prompt yang tepat
        $prompt = $this->buildPrompt($userQuery, $context);

        // 3. Generate: Kirim ke Gemini
        return $this->generateResponse($prompt);
    }

    private function retrieveRelevantData($query)
    {
        $query = strtolower($query);
        $context = "";

        // Cari data relevan dari semua tabel
        $context .= $this->getPengumumanData($query);
        // $context .= $this->getDosenData($query);
        // $context .= $this->getBeritaAlumniData($query);
        // $context .= $this->getLowonganData($query);
        // $context .= $this->getProdiData($query);
        // $context .= $this->getHimpunanData($query);

        return $context;
    }

    private function getPengumumanData($query)
    {
        if (strpos($query, 'pengumuman') !== false) {
            $pengumuman = DB::table('pengumuman')
                ->get();

            if ($pengumuman->isNotEmpty()) {
                $result = "INFORMASI PENGUMUMAN:\n";
                foreach ($pengumuman as $p) {
                    $result .= "Judul: {$p->judul}\n";
                    $result .= "Tanggal: {$p->tanggal}\n";
                    $result .= "Isi: " . substr($p->isi, 0, 200) . "...\n\n";
                }
                return $result;
            }
        }
        return "";
    }

    private function buildPrompt($userQuery, $context)
    {
        return <<<PROMPT
Anda adalah asisten virtual untuk kampus. Berikan jawaban berdasarkan informasi dari database kampus berikut:

INFORMASI DATABASE KAMPUS:
{$context}

PERTANYAAN USER: {$userQuery}

INSTRUKSI:
1. Jawab dalam Bahasa Indonesia yang baik dan sopan
2. Gunakan hanya informasi dari database di atas
3. Jika informasi tidak ditemukan, katakan dengan jujur
4. Berikan jawaban yang informatif dan membantu
5. Format jawaban dengan rapi

JAWABAN:
PROMPT;
    }

    public function generateResponse($prompt)
    {
        try {
            $response = Http::timeout(30)
                ->post('https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' . $this->apiKey, [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ]
                ]);

            if ($response->failed()) {
                throw new \Exception('Gagal mendapatkan respons dari Gemini');
            }

            $data = $response->json();

            if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                return $data['candidates'][0]['content']['parts'][0]['text'];
            } else {
                throw new \Exception('Format respons tidak sesuai');
            }
        } catch (\Exception $e) {
            Log::error('Gemini API error: ' . $e->getMessage());
            return "Maaf, saya sedang mengalami gangguan teknis. Silakan coba lagi nanti.";
        }
    }
}
