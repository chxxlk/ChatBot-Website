<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProfilHimpunanMahasiswaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('profil_himpunan_mahasiswa')->insert([
            [
                'nama_himpunan' => 'Himpunan Mahasiswa TI',
                'visi' => 'Menjadi wadah aspirasi mahasiswa TI.',
                'misi' => 'Misi: Menyelenggarakan kegiatan akademik dan non-akademik.',
                'ketua_umum' => 'Ahmad Fauzi',
                'periode' => '2025-2026',
            ],
            [
                'nama_himpunan' => 'Himpunan Mahasiswa TI',
                'visi' => 'Mengembangkan kreativitas mahasiswa TI.',
                'misi' => 'Misi: Mengadakan workshop rutin.',
                'ketua_umum' => 'Lia Kartika',
                'periode' => '2024-2025',
            ],
            [
                'nama_himpunan' => 'Himpunan Mahasiswa TI',
                'visi' => 'Meningkatkan solidaritas antar mahasiswa.',
                'misi' => 'Misi: Melaksanakan kegiatan sosial.',
                'ketua_umum' => 'Rizky Pratama',
                'periode' => '2023-2024',
            ],
            [
                'nama_himpunan' => 'Himpunan Mahasiswa TI',
                'visi' => 'Menjadi organisasi mahasiswa yang profesional.',
                'misi' => 'Misi: Membina kaderisasi berkelanjutan.',
                'ketua_umum' => 'Yuni Andira',
                'periode' => '2022-2023',
            ],
            [
                'nama_himpunan' => 'Himpunan Mahasiswa TI',
                'visi' => 'Mewujudkan mahasiswa TI yang aktif.',
                'misi' => 'Misi: Menyelenggarakan lomba internal dan eksternal.',
                'ketua_umum' => 'Fajar Nugroho',
                'periode' => '2021-2022',
            ],
            [
                'nama_himpunan' => 'Himpunan Mahasiswa TI',
                'visi' => 'Mendorong mahasiswa TI berprestasi.',
                'misi' => 'Misi: Memberikan wadah kompetisi.',
                'ketua_umum' => 'Siti Nurhaliza',
                'periode' => '2020-2021',
            ],
        ]);
    }
}
