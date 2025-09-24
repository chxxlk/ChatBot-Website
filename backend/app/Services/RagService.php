<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RagService
{
    private $chatbotIdentity;
    private $embeddingService;
    private $openRouterService;

    public function __construct()
    {
        $this->chatbotIdentity = $this->getChatbotIdentity();
        $this->embeddingService = new EmbeddingService();
        $this->openRouterService = new ModelService();
    }
    private function getChatbotIdentity()
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

        // 3. Generate: Kirim ke Openrouter
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
                    return "Nama Dosen : {$data->nama_lengkap}\nKeahlian Rekognisi: {$data->keahlian_rekognisi}\nEmail: {$data->email}\nLink: {$data->external_link}\n\n";
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

        $embeddingService = $this->embeddingService;

        foreach ($tablesConfig as $tableName => $config) {
            try {
                // Gunakan optimized semantic search dengan batch processing
                $relevantData = $embeddingService->semanticSearch(
                    $query,
                    $tableName,
                    null
                );

                if (!empty($relevantData)) {
                    $result .= "📋 **INFORMASI " . strtoupper(str_replace('_', ' ', $tableName)) . "**\n\n";

                    foreach ($relevantData as $index => $item) {
                        $result .= ($index + 1) . ". " . $config['display']($item);
                    }

                    $result .= "\n";
                }
            } catch (\Exception $e) {
                Log::error("Semantic search failed for table {$tableName}: " . $e->getMessage());
                // Fallback to traditional search
                $result .= $this->{"get" . ucfirst($tableName) . "Data"}($query, true);
            }
        }
        // Log::info($result);
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
                    $result .= "Tanggal: {$p->created_at}\n";
                    $result .= "Isi: " . substr($p->isi, 0, 500) . "...\n\n";
                }
                return $result;
            }
        }
        return "";
    }
    private function getDosenData($query)
    {
        if (strpos($query, 'dosen') !== false) {
            $dosen = DB::table('dosen')
                ->get();

            if ($dosen->isNotEmpty()) {
                $result = "INFORMASI PENGUMUMAN:\n";
                foreach ($dosen as $p) {
                    $result .= "Nama Lengkap: {$p->nama_lengkap}\n";
                    $result .= "Keahloan: {$p->keahlian_rekognisi}\n";
                    $result .= "Email: {$p->email}";
                    $result .= "Link: {$p->external_link}";
                }
                return $result;
            }
        }
        return "";
    }
    private function getLowonganData($query)
    {
        if (strpos($query, 'lowongan') !== false) {
            $lowongan = DB::table('lowongan')
                ->get();

            if ($lowongan->isNotEmpty()) {
                $result = "INFORMASI PENGUMUMAN:\n";
                foreach ($lowongan as $p) {
                    $result .= "Judul: {$p->judul}\n";
                    $result .= "Deskripsi: " . substr($p->deskripsi, 0, 200) . "\n";
                    $result .= "Tanggal: {$p->created_at}";
                }
                return $result;
            }
        }
        return "";
    }

    private function buildPrompt($userQuery, $context)
    {
        $identity = $this->chatbotIdentity;

        $identityKeywords = ['siapa kamu', 'perkenalkan diri', 'nama kamu', 'identitas', 'kamu siapa', 'perkenalan', 'kamu dibuat', 'pembuat kamu', 'developer', 'kemampuan', 'bisa apa', 'fitur'];
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
            9. Gunakan informasi database di bawah jika tersedia
            10. {$semanticInstruction}
            11. JANGAN membuat informasi jika tidak ada di database (PENTING)
            12. Jika informasi tidak lengkap, jelaskan dengan jujur (PENTING)
            13. Gunakan format yang mudah dibaca
            14. Susun jawaban dengan rapi dan jelas, tidak perlu menambahkan spasi yang belebihan
            15. Untuk list, gunakan numbering bukan bullet points
            16. Bila data tidak ada di database, jawab seadanya. Dan jangan menggunakan data dari luar databse (PENTING)
            17. Ingat dosen itu tidak termasuk kedalam pengumuman dan informasi yang bisa didapatkan tanpa menayakan secara langsung (misalnya: informasi dosen, data dosen, siapa dosen, dan lain-lain)

            # INFORMASI DATABASE KAMPUS:
            {$context}
            * Profil Program Studi Teknik Informatika 
              Program Studi Teknik Informatika UKSW adalah program studi yang berfokus pada pengembangan teknologi informasi dan komputer. Kami menyediakan pendidikan berkualitas tinggi yang mempersiapkan mahasiswa untuk menjadi profesional IT yang kompeten dan inovatif.
            * Sejarah Program Studi Teknik Informatika 
              Program Studi Teknik Informatika di Universitas Kristen Satya Wacana didirikan dengan tujuan untuk memenuhi kebutuhan dunia industri akan profesional IT yang berkualitas. 
              Sejak awal berdirinya, program studi ini telah berfokus pada pengembangan kemampuan praktikal dan teori dalam bidang teknologi informasi. Dengan kurikulum yang terus diperbarui dan fasilitas yang memadai, program studi ini bertujuan untuk menghasilkan lulusan yang siap menghadapi perkembangan teknologi yang cepat di dunia digital. 
            * Akreditasi Program Studi Teknik Infromatika Berdasarkan Keputusan LAM INFOKOM No. 086/SK/LAM-INFOKOM/Ak/S/VIII/2024, Program Studi Teknik Informatika UKSW telah mendapatkan status: AKREDITASI UNGGUL 
            * Visi dan Misi 
              - Visi Menjadi program studi Teknik Informatika terkemuka di Indonesia yang menghasilkan lulusan berkualitas tinggi, inovatif, dan berkompeten dalam pengembangan dan penerapan teknologi informasi untuk kemajuan masyarakat dan industri. 
              - Misi 1. Menyelenggarakan pendidikan berkualitas tinggi di bidang Teknik Informatika. 2. Melakukan penelitian dan pengembangan teknologi informasi yang bermanfaat. 3. Menjalin kerjasama dengan industri dan masyarakat dalam penerapan teknologi informasi. 4. Mengembangkan karakter mahasiswa yang berkualitas dan profesional. 
            * Layanan Kampus 1. Siasat (http://siasat.uksw.edu/) 2. Sistem Informasi Tugas Akhir FTI UKSW (http://online.fti.uksw.edu/) 3. IT-Explore : Jurnal Penerapan Teknologi Informasi dan Komunikasi 4. Perpustakaan E-Library UKSW 5. AITI : Jurnal Teknologi Informasi (https://ejournal.uksw.edu/aiti) 
            * Perusahaan Kerjasama 1. Alfamart 2. CTI Group 3. PT. Purabarutama 4. PT. Sinarmas 5. BANK BCA

            # PERTANYAAN USER:
            {$userQuery}

            # FORMAT JAWABAN:
            - Awali dengan salam jika appropriate
            - Referensikan informasi dari database jika relevan
            - Akhiri dengan penawaran bantuan lebih lanjut
            - Boleh tambahkan emote jika appropriate (makasimal 5 emoji)
            - Bold untuk judul dan informasi penting
            - pisahkan section dengan newlines

            JAWABAN:
        PROMPT;
    }

    private function analyzeQueryType($query)
    {
        $query = strtolower($query);
        $types = [];

        if (preg_match('/(pengumuman|announcement|news|berita)/', $query)) $types[] = 'PENGUMUMAN';
        if (preg_match('/(lowongan|job|vacancy|asisten)/', $query)) $types[] = 'LOWONGAN';
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

        switch (true) {
            case str_contains($query, 'siapa kamu') || str_contains($query, 'perkenalkan diri') || str_contains($query, 'nama kamu') || str_contains($query, 'kamu siapa' || str_contains($query, 'perkenalan')):
                $response = "Halo! Saya {$identity['name']}, {$identity['role']} dari ";
                $response .= "{$identity['department']} di {$identity['university']}. ";
                $response .= "Saya di sini untuk membantu Anda dengan berbagai informasi seputar kampus. ";
                $response .= "Saya dapat memberikan informasi tentang pengumuman, program studi, ";
                $response .= "himpunan mahasiswa, lowongan asisten dosen, berita alumni, dan informasi dosen. ";
                $response .= "Ada yang bisa saya bantu hari ini? 😊";

                return $response;
            case str_contains($query, 'kamu dibuat') || str_contains($query, 'pembuat kamu') || str_contains($query, 'developer'):
                $response = "Saya {$identity['name']} dikembangkan oleh tim Program Studi Teknologi Informasi ";
                $response .= "{$identity['university']} untuk membantu memberikan informasi kampus secara cepat dan akurat. ";
                $response .= "Saya menggunakan teknologi AI yang terintegrasi dengan database kampus untuk memberikan ";
                $response .= "respons yang tepat dan informatif.";

                return $response;

            case str_contains($query, 'kemampuan') || str_contains($query, 'bisa apa') || str_contains($query, 'fitur'):
                $response = "Sebagai {$identity['role']}, saya dapat membantu Anda dengan: \n\n";
                $response .= "* 📋 Informasi Pengumuman - pengumuman terbaru, pengumuman penting\n";
                $response .= "* 🎓 Program Studi - informasi tentang TI, kurikulum, akreditasi\n";
                $response .= "* 👥 Himpunan Mahasiswa - profil HMTI, kegiatan, kepengurusan\n";
                $response .= "* 💼 Lowongan Asisten - lowongan asisten dosen, persyaratan\n";
                $response .= "* 📰 Berita Alumni - kesuksesan alumni, kegiatan alumni\n\n";
                $response .= "* 👨‍🏫 Informasi Dosen - profil dosen, bidang keahlian\n\n";
                $response .= "Ada yang spesifik yang ingin Anda tanyakan?";

                return $response;

            default:
                return false;
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

        $identityKeywords = ['siapa kamu', 'perkenalkan', 'kamu siapa', 'identitas', 'perkenalan', 'nama kamu', 'kamu siapa', 'kamu dibuat', 'pembuat kamu', 'developer', 'kemampuan', 'bisa apa', 'fitur'];
        $greetingKeywords = ['halo', 'hai', 'hello', 'hi', 'selamat pagi', 'selamat siang', 'selamat sore', 'selamat malam'];
        $generalKeywords = ['help', 'bantuan', 'bantu', 'bisa apa', 'fitur'];

        foreach ($identityKeywords as $keyword) {
            if (str_contains($query, $keyword) !== false) return true;
        }

        foreach ($greetingKeywords as $keyword) {
            if (str_contains($query, $keyword) !== false) return true;
        }

        foreach ($generalKeywords as $keyword) {
            if (str_contains($query, $keyword) !== false) return true;
        }

        // Jika query spesifik tentang konten, tidak perlu perkenalan
        $contentKeywords = ['pengumuman', 'dosen', 'prodi', 'himpunan', 'lowongan', 'alumni', 'jadwal', 'matkul'];
        foreach ($contentKeywords as $keyword) {
            if (str_contains($query, $keyword) !== false) return false;
        }

        // Default: tidak perlu perkenalan
        return false;
    }

    public function generateResponse($prompt)
    {
        try {
            return $this->openRouterService->generateResponse($prompt);
        } catch (\Exception $e) {
            Log::error('RAG Service Error: ' . $e->getMessage());
            return "Maaf, saya sedang mengalami gangguan teknis. Silakan coba lagi nanti.";
        }
    }

    public function getChatbotInfo()
    {
        return $this->chatbotIdentity;
    }
}
