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
                'matakuliah' => 'Pemrograman Web',
                'kualifikasi' => 'Minimal semester 5, menguasai HTML, CSS, dan JavaScript.',
                'deadline' => '2025-09-20',
                'kontak' => 'webti@kampus.ac.id',
            ],
            [
                'matakuliah' => 'Basis Data',
                'kualifikasi' => 'IPK minimal 3.25, mampu mengajar SQL.',
                'deadline' => '2025-09-22',
                'kontak' => 'dbti@kampus.ac.id',
            ],
            [
                'matakuliah' => 'Jaringan Komputer',
                'kualifikasi' => 'Menguasai jaringan dasar, konfigurasi router, dan Linux.',
                'deadline' => '2025-09-25',
                'kontak' => 'jarkomti@kampus.ac.id',
            ],
            [
                'matakuliah' => 'Kecerdasan Buatan',
                'kualifikasi' => 'Menguasai Python dan library AI.',
                'deadline' => '2025-09-28',
                'kontak' => 'aisti@kampus.ac.id',
            ],
            [
                'matakuliah' => 'Sistem Operasi',
                'kualifikasi' => 'Minimal semester 6, menguasai Linux.',
                'deadline' => '2025-09-29',
                'kontak' => 'sosti@kampus.ac.id',
            ],
            [
                'matakuliah' => 'Rekayasa Perangkat Lunak',
                'kualifikasi' => 'Memiliki pengalaman proyek software.',
                'deadline' => '2025-09-30',
                'kontak' => 'rplti@kampus.ac.id',
            ],
        ]);
    }
}
