<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LowonganAsistenDosenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('lowongan_asisten_dosen')->insert([
            [
                'mata_kuliah' => 'Pemrograman Web',
                'kulifikasi' => 'Minimal semester 5, menguasai HTML, CSS, dan JavaScript.',
                'deadline' => '2025-09-20',
                'kontak' => 'webti@kampus.ac.id',
            ],
            [
                'mata_kuliah' => 'Basis Data',
                'kulifikasi' => 'IPK minimal 3.25, mampu mengajar SQL.',
                'deadline' => '2025-09-22',
                'kontak' => 'dbti@kampus.ac.id',
            ],
            [
                'mata_kuliah' => 'Jaringan Komputer',
                'kulifikasi' => 'Menguasai jaringan dasar, konfigurasi router, dan Linux.',
                'deadline' => '2025-09-25',
                'kontak' => 'jarkomti@kampus.ac.id',
            ],
            [
                'mata_kuliah' => 'Kecerdasan Buatan',
                'kulifikasi' => 'Menguasai Python dan library AI.',
                'deadline' => '2025-09-28',
                'kontak' => 'aisti@kampus.ac.id',
            ],
            [
                'mata_kuliah' => 'Sistem Operasi',
                'kulifikasi' => 'Minimal semester 6, menguasai Linux.',
                'deadline' => '2025-09-29',
                'kontak' => 'sosti@kampus.ac.id',
            ],
            [
                'mata_kuliah' => 'Rekayasa Perangkat Lunak',
                'kulifikasi' => 'Memiliki pengalaman proyek software.',
                'deadline' => '2025-09-30',
                'kontak' => 'rplti@kampus.ac.id',
            ],
        ]);
    }
}
