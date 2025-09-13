<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProfilProdiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('profil_prodi')->insert([
            [
                'visi' => 'Menjadi program studi unggulan di bidang teknologi informasi.',
                'misi' => 'Misi: Mendidik mahasiswa agar kompeten di bidang TI.',
                'akreditasi' => 'A',
                'ketua_prodi' => 'Dr. Anton Wijaya',
            ],
            [
                'visi' => 'Menghasilkan lulusan TI yang berintegritas dan profesional.',
                'misi' => 'Misi: Menjalankan kurikulum berbasis industri.',
                'akreditasi' => 'A',
                'ketua_prodi' => 'Dr. Ratna Puspita',
            ],
            [
                'visi' => 'Menjadi pusat penelitian teknologi informasi di Jawa Tengah.',
                'misi' => 'Misi: Melaksanakan penelitian terapan di bidang TI.',
                'akreditasi' => 'A',
                'ketua_prodi' => 'Dr. Bagus Santoso',
            ],
            [
                'visi' => 'Membangun jejaring industri yang kuat.',
                'misi' => 'Misi: Meningkatkan kerjasama dengan industri TI.',
                'akreditasi' => 'A',
                'ketua_prodi' => 'Dr. Andini Maharani',
            ],
            [
                'visi' => 'Mengembangkan inovasi digital untuk masyarakat.',
                'misi' => 'Misi: Memberikan solusi digital yang bermanfaat.',
                'akreditasi' => 'A',
                'ketua_prodi' => 'Dr. Rudi Hartanto',
            ],
            [
                'visi' => 'Meningkatkan mutu pembelajaran berbasis AI.',
                'misi' => 'Misi: Mengintegrasikan AI dalam pembelajaran.',
                'akreditasi' => 'A',
                'ketua_prodi' => 'Dr. Lina Susanti',
            ],
        ]);
    }
}
