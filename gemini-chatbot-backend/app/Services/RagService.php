<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RagService
{
    private $apiKey;
    private $chatbotIdentity;
    private $embeddingService;

    public function __construct()
    {
        $this->apiKey = env('GEMINI_API_KEY');
        $this->chatbotIdentity = $this->getChatbotIdentity();
        $this->embeddingService = new EmbeddingService();
    }

    private function getChatbotIdentity()
    {
        return [
            'name' => 'Fernando',
            'role' => 'Asisten Virtual',
            'department' => 'Program Studi Teknologi Informasi',
            'university' => 'Universitas Kristen Satya Wacana',
            'tone' => 'ramah, sopan, dan informatif',
            'language' => 'Bahasa Indonesia yang baik dan benar',
            'limitations' => 'Hanya dapat menjawab berdasarkan informasi dari database kampus'
        ];
    }


    public function queryWithContext($userQuery)
    {
        // 0. Cek dulu jika ini pertanyaan tentang identitas
        $identityResponse = $this->handleIdentityQuery($userQuery);
        if ($identityResponse) {
            return $identityResponse;
        }

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

        // Identitas Bot
        $context .= "INFORMASI CHATBOT:\n";
        $context .= "Nama: {$this->chatbotIdentity['name']}\n";
        $context .= "Peran: {$this->chatbotIdentity['role']}\n";
        $context .= "Program Studi: {$this->chatbotIdentity['department']}\n";
        $context .= "Universitas: {$this->chatbotIdentity['university']}\n\n";

        // Gunakan semantic search untuk mencari data yang relevan
        $context .= $this->getSemanticRelevantData($query);

        return $context;
    }

    public function getSemanticRelevantData($query)
    {
        $result = "";
        
        // Konfigurasi semantic search untuk setiap tabel
        $tablesConfig = [
            'pengumuman' => [
                'columns' => ['judul', 'isi'],
                'display' => function ($item) {
                    return "Judul: {$item->judul}\nTanggal: {$item->tanggal}\nIsi: " . substr($item->isi, 0, 200) . "...\n\n";
                }
            ],
            'profil_prodi' => [
                'columns' => ['visi', 'misi', 'ketua_prodi'],
                'display' => function ($item) {
                    return "Visi: " . substr($item->visi, 0, 200) . "\nMisi: " . substr($item->misi, 0, 200) . "\nKetua Prodi: {$item->ketua_prodi}\n\n";
                }
            ],
            'profil_himpunan_mahasiswa' => [
                'columns' => ['visi', 'misi', 'ketua_umum'],
                'display' => function ($item) {
                    return "Visi: " . substr($item->visi, 0, 200) . "\nMisi: " . substr($item->misi, 0, 200) . "\nKetua Umum: {$item->ketua_umum}\nPeriode: {$item->periode}\n\n";
                }
            ],
            'lowongan_asisten_dosen' => [
                'columns' => ['mata_kuliah', 'kualifikasi'],
                'display' => function ($item) {
                    return "Mata Kuliah: {$item->mata_kuliah}\nKualifikasi: " . substr($item->kualifikasi, 0, 200) . "\nDeadline: {$item->deadline}\nKontak: {$item->kontak}\n\n";
                }
            ],
            'berita_alumni' => [
                'columns' => ['nama_alumni', 'judul_berita', 'isi'],
                'display' => function ($item) {
                    return "Nama Alumni: {$item->nama_alumni}\nJudul Berita: {$item->judul_berita}\nIsi: " . substr($item->isi, 0, 200) . "\nTanggal: {$item->tanggal}\n\n";
                }
            ]
        ];

        foreach ($tablesConfig as $tableName => $config) {
            try {
                $relevantData = $this->embeddingService->semanticSearch(
                    $query, 
                    $tableName, 
                    $config['columns'],
                    null, // limit
                    0.3 // threshold similarity
                );

                if (!empty($relevantData)) {
                    $result .= "INFORMASI " . strtoupper(str_replace('_', ' ', $tableName)) . ":\n";
                    
                    foreach ($relevantData as $item) {
                        $result .= $config['display']($item);
                    }
                    
                    $result .= "\n";
                }
            } catch (\Exception $e) {
                Log::error("Semantic search failed for table {$tableName}: " . $e->getMessage());
                // Fallback to traditional search
                $result .= $this->{"get" . ucfirst($tableName) . "Data"}($query, true);
            }
        }

        return $result;
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
                    $result .= "Isi: " . substr($p->isi, 0, 500) . "...\n\n";
                }
                return $result;
            }
        }
        return "";
    }

    private function getProdiData($query)
    {
        if (strpos($query, 'prodi') !== false) {
            $prodi = DB::table('profil_prodi')
                ->get();

            if ($prodi->isNotEmpty()) {
                $result = "INFORMASI PROGRAM STUDI:\n";
                foreach ($prodi as $p) {
                    $result .= "Visi: " . substr($p->visi, 0, 200) . "\n";
                    $result .= "Misi: " . substr($p->misi, 0, 200) . "\n";
                    $result .= "Ketua Prodi: {$p->ketua_prodi}\n";
                }
                return $result;
            }
        }
        return "";
    }

    private function getHimpunanData($query)
    {
        if (strpos($query, 'himpunan mahasiswa') !== false) {
            $himpunan = DB::table('profil_himpunan_mahasiswa')
                ->get();

            if ($himpunan->isNotEmpty()) {
                $result = "INFORMASI HIMPUNAN MAHASISWA:\n";
                foreach ($himpunan as $h) {
                    $result .= "Visi: " . substr($h->visi, 0, 200) . "\n";
                    $result .= "Misi: " . substr($h->misi, 0, 200) . "\n";
                    $result .= "Ketua Umum: {$h->ketua_umum}\n";
                    $result .= "Periode: {$h->periode}\n";
                }
                return $result;
            }
        }
        return "";
    }

    private function getLowonganAsistenData($query)
    {
        if (strpos($query, 'lowongan') !== false || strpos($query, 'asisten') !== false || strpos($query, 'asisten dosen') !== false) {
            $lowongan = DB::table('lowongan_asisten_dosen')
                ->get();

            if ($lowongan->isNotEmpty()) {
                $result = "INFORMASI HIMPUNAN MAHASISWA:\n";
                foreach ($lowongan as $l) {
                    $result .= "Matakuliah: {$l->mata_kuliah}\n";
                    $result .= "Kualifikasi : " . substr($l->kualifikasi, 0, 200) . "\n";
                    $result .= "Deadline : {$l->deadline}\n";
                    $result .= "Kontak : {$l->kontak}\n";
                }
                return $result;
            }
        }
        return "";
    }

    private function getBeritaAlumniData($query)
    {
        if (strpos($query, 'alumni') !== false || strpos($query, 'berita') !== false || strpos($query, 'berita alumni') !== false) {
            $alumni = DB::table('berita_alumni')
                ->get();

            if ($alumni->isNotEmpty()) {
                $result = "INFORMASI ALUMNI:\n";
                foreach ($alumni as $a) {
                    $result .= "Nama Alumni: {$a->nama_alumni}\n";
                    $result .= "Judul Berita : {$a->judul_berita}\n";
                    $result .= "Isi : " . substr($a->isi, 0, 200) . "\n";
                    $result .= "Tanggal : {$a->tanggal}\n";
                }
                return $result;
            }
        }
        return "";
    }

    /**
     * Membuat prompt untuk inputan AI.
     *
     * Prompt ini dibuat berdasarkan query user dan informasi database kampus.
     * Prompt ini akan digunakan sebagai inputan untuk AI.
     *
     * @param string $userQuery query yang diinputkan oleh user
     * @param string $context informasi database kampus yang relevan
     * @return string prompt yang dibuat
     */
    private function buildPrompt($userQuery, $context)
    {
        $identity = $this->chatbotIdentity;

        $identityKeywords = ['siapa kamu', 'perkenalkan diri', 'nama kamu', 'identitas', 'kamu siapa'];
        $isIdentityQuery = false;
        $needsIntroduction = $this->needsIntroduction($userQuery);

        foreach ($identityKeywords as $keyword) {
            if (strpos(strtolower($userQuery), $keyword) !== false) {
                $isIdentityQuery = true;
                break;
            }
        }

        $queryType = $this->analyzeQueryType($userQuery);
        $isSemanticMatch = !empty(trim($context)) && strpos($context, 'INFORMASI') !== false;

        $relevansiDatabase = $isSemanticMatch ? 'YA' : 'TIDAK';

        $identityInstruction = $isIdentityQuery ?
            "PERTANYAAN INI TENTANG IDENTITAS ANDA. JAWAB DENGAN MENGGUNAKAN INFORMASI IDENTITAS DI BAWAH DAN JANGAN MENYANGKALNYA." :
            "Jika ditanya tentang identitas, jawab menggunakan informasi identitas di bawah.";

        $introductionInstruction = $needsIntroduction ?
            "Jika appropriate, awali dengan perkenalan singkat sebagai {$identity['name']}." :
            "JANGAN awali dengan perkenalan diri. Langsung berikan jawaban yang diminta.";

        $semanticInstruction = $isSemanticMatch ?
            "PERTANYAAN INI RELEVAN DENGAN INFORMASI DATABASE. GUNAKAN INFORMASI DI BAWAH UNTUK MENJAWAB DENGAN AKURAT." :
            "INFORMASI DATABASE TERBATAS. JAWAB BERDASARKAN PENGETAHUAN UMUM TENTANG PERAN ANDA SEBAGAI ASISTEN KAMPUS.";

        return <<<PROMPT
            # PERINTAH KERAS DAN INSTRUKSI
            ANDA ADALAH {$identity['name']}, {$identity['role']} dari {$identity['department']} di {$identity['university']}.
            ANDA BUKAN model bahasa AI umum. ANDA ADALAH asisten virtual khusus untuk kampus.

            # ANALISIS PERTANYAAN:
            - JENIS: {$queryType}
            - RELEVANSI DATABASE: {$relevansiDatabase}

            # IDENTITAS ANDA:
            - NAMA: {$identity['name']}
            - PERAN: {$identity['role']}
            - PROGRAM STUDI: {$identity['department']}
            - UNIVERSITAS: {$identity['university']}
            - BAHASA: {$identity['language']}
            - GAYA BICARA: {$identity['tone']}

            # INSTRUKSI KHUSUS:
            1. {$identityInstruction}
            2. JANGAN PERNAH mengatakan bahwa Anda adalah model bahasa AI atau tidak memiliki identitas
            3. SELALU akui bahwa Anda adalah {$identity['name']}, {$identity['role']} dari {$identity['department']}
            4. Jika ditanya tentang kemampuan, jelaskan bahwa Anda membantu dengan informasi dari database kampus
            5. Gunakan nada yang {$identity['tone']} dan profesional
            6. {$introductionInstruction}
            7. Untuk pertanyaan factual (pengumuman, dosen, dll), langsung berikan jawaban tanpa perkenalan
            8. Hanya perkenalkan diri jika ditanya tentang identitas atau untuk greeting
            9. Jawab dengan nada yang {$identity['tone']}
            10. Gunakan informasi database di bawah jika tersedia
            
            # INSTRUKSI KHUSUS LAINNYA:
            1. {$semanticInstruction}
            2. JANGAN membuat informasi jika tidak ada di database
            3. Jika informasi tidak lengkap, jelaskan dengan jujur
            4. Gunakan format yang mudah dibaca

            # INFORMASI DATABASE KAMPUS:
            {$context}

            # PERTANYAAN USER:
            {$userQuery}

            # FORMAT JAWABAN:
            - Awali dengan salam jika appropriate
            - Jawab dengan menggunakan informasi identitas Anda
            - Referensikan informasi dari database jika relevan
            - Akhiri dengan penawaran bantuan lebih lanjut

            JAWABAN:
        PROMPT;
    }

     private function analyzeQueryType($query)
    {
        $query = strtolower($query);
        $types = [];

        if (preg_match('/(pengumuman|announcement|news)/', $query)) $types[] = 'PENGUMUMAN';
        if (preg_match('/(prodi|program studi|jurusan|major)/', $query)) $types[] = 'PROGRAM_STUDI';
        if (preg_match('/(himpunan|organisasi|hmti)/', $query)) $types[] = 'HIMPUNAN';
        if (preg_match('/(lowongan|job|vacancy|asisten)/', $query)) $types[] = 'LOWONGAN';
        if (preg_match('/(alumni|graduate|wisuda)/', $query)) $types[] = 'ALUMNI';
        if (preg_match('/(dosen|lecturer|pengajar)/', $query)) $types[] = 'DOSEN';

        if (empty($types)) {
            return 'UMUM';
        }

        return implode(' + ', $types);
    }

    private function handleIdentityQuery($userQuery)
    {
        $identity = $this->chatbotIdentity;
        $query = strtolower($userQuery);

        if (
            strpos($query, 'siapa kamu') !== false ||
            strpos($query, 'perkenalkan diri') !== false ||
            strpos($query, 'nama kamu') !== false ||
            strpos($query, 'kamu siapa') !== false
        ) {

            $response = "Halo! Saya {$identity['name']}, {$identity['role']} dari ";
            $response .= "{$identity['department']} di {$identity['university']}. ";
            $response .= "Saya di sini untuk membantu Anda dengan berbagai informasi seputar kampus. ";
            $response .= "Saya dapat memberikan informasi tentang pengumuman, program studi, ";
            $response .= "himpunan mahasiswa, lowongan asisten dosen, berita alumni, dan informasi dosen. ";
            $response .= "Ada yang bisa saya bantu hari ini? 😊";

            return $response;
        }

        if (
            strpos($query, 'kamu dibuat') !== false ||
            strpos($query, 'pembuat kamu') !== false ||
            strpos($query, 'developer') !== false
        ) {

            $response = "Saya {$identity['name']} dikembangkan oleh tim Program Studi Teknologi Informasi ";
            $response .= "{$identity['university']} untuk membantu memberikan informasi kampus secara cepat dan akurat. ";
            $response .= "Saya menggunakan teknologi AI yang terintegrasi dengan database kampus untuk memberikan ";
            $response .= "respons yang tepat dan informatif.";

            return $response;
        }

        if (
            strpos($query, 'kemampuan') !== false ||
            strpos($query, 'bisa apa') !== false ||
            strpos($query, 'fitur') !== false
        ) {

            $response = "Sebagai {$identity['role']}, saya dapat membantu Anda dengan: \n\n";
            $response .= "📋 **Informasi Pengumuman** - pengumuman terbaru, pengumuman penting\n";
            $response .= "🎓 **Program Studi** - informasi tentang TI, kurikulum, akreditasi\n";
            $response .= "👥 **Himpunan Mahasiswa** - profil HMTI, kegiatan, kepengurusan\n";
            $response .= "💼 **Lowongan Asisten** - lowongan asisten dosen, persyaratan\n";
            $response .= "📰 **Berita Alumni** - kesuksesan alumni, kegiatan alumni\n";
            $response .= "👨‍🏫 **Informasi Dosen** - profil dosen, bidang keahlian\n\n";
            $response .= "Ada yang spesifik yang ingin Anda tanyakan?";

            return $response;
        }

        return null;
    }
    private function needsIntroduction($query)
    {
        $query = strtolower($query);

        // Hanya perlu perkenalan untuk:
        // 1. Pertanyaan tentang identitas
        // 2. Greeting/sapaan
        // 3. Pertanyaan umum tanpa konteks spesifik

        $identityKeywords = ['siapa kamu', 'perkenalkan', 'kamu siapa', 'identitas'];
        $greetingKeywords = ['halo', 'hai', 'hello', 'hi', 'selamat pagi', 'selamat siang', 'selamat sore', 'selamat malam'];
        $generalKeywords = ['help', 'bantuan', 'bantu', 'bisa apa', 'fitur'];

        foreach ($identityKeywords as $keyword) {
            if (strpos($query, $keyword) !== false) return true;
        }

        foreach ($greetingKeywords as $keyword) {
            if (strpos($query, $keyword) !== false) return true;
        }

        foreach ($generalKeywords as $keyword) {
            if (strpos($query, $keyword) !== false) return true;
        }

        // Jika query spesifik tentang konten, tidak perlu perkenalan
        $contentKeywords = ['pengumuman', 'dosen', 'prodi', 'himpunan', 'lowongan', 'alumni', 'jadwal', 'matkul'];
        foreach ($contentKeywords as $keyword) {
            if (strpos($query, $keyword) !== false) return false;
        }

        // Default: tidak perlu perkenalan
        return false;
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
                    ],
                    'generationConfig' => [
                        'temperature' => 0.3,
                        'topK' => 20,
                        'topP' => 0.8,
                        'maxOutputTokens' => 1024,
                    ],
                    'safetySettings' => [
                        [
                            'category' => 'HARM_CATEGORY_HARASSMENT',
                            'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                        ],
                        [
                            'category' => 'HARM_CATEGORY_HATE_SPEECH',
                            'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                        ]
                    ]
                ]);

            if ($response->failed()) {
                throw new \Exception('Gagal mendapatkan respons dari Gemini');
            }

            $data = $response->json();

            if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                $responseText = $data['candidates'][0]['content']['parts'][0]['text'];

                // Post-processing: Pastikan response tidak mengandung penyangkalan identitas
                $denialPatterns = [
                    '/sebagai (ai|artificial intelligence)/i',
                    '/sebagai model bahasa/i',
                    '/saya tidak memiliki identitas/i',
                    '/saya tidak memiliki nama/i'
                ];

                foreach ($denialPatterns as $pattern) {
                    if (preg_match($pattern, $responseText)) {
                        // Ganti dengan response identitas yang benar
                        return $this->getDefaultIdentityResponse();
                    }
                }

                return $responseText;
            } else {
                Log::error('Gemini response format: ' . json_encode($data));
                throw new \Exception('Format respons tidak sesuai');
            }
        } catch (\Exception $e) {
            Log::error('Gemini API error: ' . $e->getMessage());
            return "Maaf, saya sedang mengalami gangguan teknis. Silakan coba lagi nanti.";
        }
    }

    /**
     * Method untuk mendapatkan informasi identitas chatbot
     */
    public function getChatbotInfo()
    {
        return $this->chatbotIdentity;
    }

    private function getDefaultIdentityResponse()
    {
        $identity = $this->chatbotIdentity;
        return "Halo! Saya {$identity['name']}, {$identity['role']} dari " .
            "{$identity['department']} di {$identity['university']}. " .
            "Saya di sini untuk membantu Anda dengan informasi seputar kampus. " .
            "Ada yang bisa saya bantu hari ini?";
    }
}
