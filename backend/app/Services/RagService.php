<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RagService
{
    private $chatbotIdentity;
    private $embeddingService;
    private $modelService;

    public function __construct()
    {
        $this->chatbotIdentity = $this->getChatbotIdentity();
        $this->embeddingService = new EmbeddingService();
        $this->modelService = new ModelService();
    }

    private function getChatbotIdentity(): array
    {
        return [
            'name' => 'Mr, Wacana',
            'role' => 'Asisten Virtual',
            'department' => 'Program Studi Teknologi Informasi',
            'university' => 'Universitas Kristen Satya Wacana',
            'tone' => 'ramah, sopan, dan informatif',
            'language' => 'Bahasa Indonesia yang baik dan benar',
            'limitations' => 'Hanya dapat menjawab berdasarkan informasi dari database kampus'
        ];
    }

    /**
     * Streaming version: panggil model secara streaming dan terus kirim chunk ke callback.
     *
     * @param string $userQuery
     * @param callable $onChunk menerima potongan teks
     * @return void
     */
    public function queryWithContextStream(string $userQuery, callable $onChunk): void
    {
        // cek identitas dulu
        $identityResponse = $this->handleIdentityQuery($userQuery);
        if ($identityResponse !== false) {
            // langsung kirim sebagai satu chunk
            $onChunk($identityResponse);
            // bisa juga kirim sinyal done setelah ini, tapi controller akan tangani “done”
            return;
        }

        // retrieve context
        $context = $this->retrieveRelevantData($userQuery);

        // build prompt
        $prompt = $this->buildPrompt($userQuery, $context);

        try {
            $this->modelService->generateStreamedResponse($prompt, function ($partial) use ($onChunk) {
                $onChunk($partial);
            });
        } catch (\Exception $e) {
            Log::error('RAG Service Streaming Error: ' . $e->getMessage());
            $onChunk("Maaf, terjadi kesalahan teknis: " . $e->getMessage());
        }
    }

    // Metode non-stream (fallback)
    public function queryWithContext(string $userQuery): string
    {
        // cek identitas
        $identityResponse = $this->handleIdentityQuery($userQuery);
        if ($identityResponse !== false) {
            return $identityResponse;
        }
        $context = $this->retrieveRelevantData($userQuery);
        $prompt = $this->buildPrompt($userQuery, $context);
        try {
            return $this->modelService->generateResponseOnce($prompt);
        } catch (\Exception $e) {
            Log::error('RAG Service Error (non-stream): ' . $e->getMessage());
            return "Maaf, saya sedang mengalami gangguan teknis. Silakan coba lagi nanti.";
        }
    }

    /****************** Bagian helper (tidak diubah banyak) ******************/

    private function retrieveRelevantData(string $query): string
    {
        $q = strtolower($query);
        $context = "";

        $context .= "INFORMASI CHATBOT:\n";
        $context .= "Nama: {$this->chatbotIdentity['name']}\n";
        $context .= "Peran: {$this->chatbotIdentity['role']}\n";
        $context .= "Program Studi: {$this->chatbotIdentity['department']}\n";
        $context .= "Universitas: {$this->chatbotIdentity['university']}\n\n";

        $context .= $this->getSemanticRelevantData($q);

        return $context;
    }

    public function getSemanticRelevantData(string $query)
    {
        $result = "";
        $tablesConfig = [
            'pengumuman' => [
                'columns' => ['judul', 'isi', 'kategori', 'created_at'],
                'display' => function ($item) {
                    $data = $item['data'];
                    return "Judul: {$data->judul}\nKategori: {$data->kategori}\nTanggal: {$data->created_at}\nIsi: " . substr($data->isi, 0, 200) . "...\n\n";
                }
            ],
            'dosen' => [
                'columns' => ['nama_lengkap', 'keahlian_rekognisi', 'email', 'external_link'],
                'display' => function ($item) {
                    $data = $item['data'];
                    return "Nama Dosen: {$data->nama_lengkap}\nKeahlian: {$data->keahlian_rekognisi}\nEmail: {$data->email}\nLink: {$data->external_link}\n\n";
                }
            ],
            'lowongan' => [
                'columns' => ['judul', 'deskripsi', 'link_pendaftaran', 'created_at'],
                'display' => function ($item) {
                    $data = $item['data'];
                    return "Judul: {$data->judul}\nDeskripsi: {$data->deskripsi}\nLink: {$data->link_pendaftaran}\nTanggal: {$data->created_at}\n\n";
                }
            ],
        ];

        foreach ($tablesConfig as $tableName => $config) {
            try {
                $relevantData = $this->embeddingService->semanticSearch($query, $tableName, 5);
                if (!empty($relevantData)) {
                    $result .= "📋 **INFORMASI " . strtoupper(str_replace('_', ' ', $tableName)) . "**\n\n";
                    foreach ($relevantData as $idx => $item) {
                        $result .= ($idx + 1) . ". " . $config['display']($item);
                    }
                    $result .= "\n";
                }
            } catch (\Exception $e) {
                Log::error("Semantic search failed for {$tableName}: " . $e->getMessage());
                // fallback — kamu bisa isi fallback sesuai keinginan
            }
        }

        return $result;
    }

    private function handleIdentityQuery(string $userQuery)
    {
        $q = strtolower($userQuery);
        $identity = $this->chatbotIdentity;

        if (str_contains($q, 'siapa kamu') || str_contains($q, 'nama kamu') || str_contains($q, 'perkenalkan diri')) {
            return "Halo! Saya {$identity['name']}, {$identity['role']} dari {$identity['department']} di {$identity['university']}. Ada yang bisa saya bantu hari ini?";
        }
        if (str_contains($q, 'kamu dibuat') || str_contains($q, 'pembuat kamu') || str_contains($q, 'developer')) {
            return "Saya {$identity['name']} dikembangkan oleh tim Program Studi Teknologi Informasi di {$identity['university']}.";
        }
        if (str_contains($q, 'kemampuan') || str_contains($q, 'bisa apa') || str_contains($q, 'fitur')) {
            return "Sebagai {$identity['role']}, saya bisa membantu Anda dengan informasi pengumuman, data dosen, lowongan, dan informasi kampus lainnya.";
        }
        return false;
    }

    private function analyzeQueryType(string $query): string
    {
        $q = strtolower($query);
        $types = [];

        if (preg_match('/(pengumuman|announcement|berita)/', $q)) $types[] = 'PENGUMUMAN';
        if (preg_match('/(lowongan|asisten|job)/', $q)) $types[] = 'LOWONGAN';
        if (preg_match('/(dosen|lecturer)/', $q)) $types[] = 'DOSEN';

        if (empty($types)) {
            return 'UMUM';
        }
        return implode(' + ', $types);
    }

    private function needsIntroduction(string $userQuery): bool
    {
        $q = strtolower($userQuery);
        $identityKeywords = ['siapa kamu', 'perkenalkan', 'nama kamu'];
        $greetingKeywords = ['halo', 'hai', 'selamat pagi', 'selamat siang'];
        $generalKeywords = ['help', 'bantuan', 'fitur'];

        foreach (array_merge($identityKeywords, $greetingKeywords, $generalKeywords) as $kw) {
            if (str_contains($q, $kw)) {
                return true;
            }
        }

        $contentKeywords = ['pengumuman', 'dosen', 'lowongan', 'alumni'];
        foreach ($contentKeywords as $kw) {
            if (str_contains($q, $kw)) {
                return false;
            }
        }

        return false;
    }

    private function buildPrompt(string $userQuery, string $context): string
    {
        $identity = $this->chatbotIdentity;
        $queryType = $this->analyzeQueryType($userQuery);
        $isSemanticMatch = !empty(trim($context)) && strpos($context, 'INFORMASI') !== false;
        $relevansiDatabase = $isSemanticMatch ? 'YA' : 'TIDAK';

        $identityInstruction = "Jika ditanya tentang identitas, jawab dengan identitas di bawah.";
        $needsIntro = $this->needsIntroduction($userQuery);
        $introInstruction = $needsIntro
            ? "Jika perlu, awali dengan perkenalan singkat."
            : "Langsung jawab tanpa perkenalan.";

        $semanticInstruction = $isSemanticMatch
            ? "Gunakan informasi dari database siswa."
            : "Jawab berdasarkan pengetahuan umum.";

        return <<<PROMPT
# IDENTITAS & SETTING
Anda adalah {$identity['name']}, {$identity['role']} dari {$identity['department']} di {$identity['university']}.  
Bahasa: {$identity['language']}. Gaya: {$identity['tone']}.  

# ANALISIS
Jenis pertanyaan: {$queryType}  
Relevansi database: {$relevansiDatabase}  

# INSTRUKSI
1. {$identityInstruction}  
2. {$introInstruction}  
3. {$semanticInstruction}  
4. Jangan buat data jika tidak ada  
5. Format teks rapik dan sopan

# INFORMASI DATABASE  
{$context}  

# PERTANYAAN USER  
{$userQuery}  

#FORMAT JAWABAN
1. Jawaban singkat dan sopan
2. Tambahkah emote jika apropriate
3. Gunakan bullet point untuk jawaban dengan list
4. Huruf tebal untuk bagian yang penting.

JAWABAN:
PROMPT;
    }
    public function getChatbotInfo()
    {
        return $this->chatbotIdentity;
    }
}
