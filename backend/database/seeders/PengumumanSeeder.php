<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PengumumanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('pengumuman')->insert([
            [
                'judul' => 'Ujian Tengah Semester Ganjil 2025',
                'isi' => 'UTS akan dilaksanakan pada tanggal 10-15 Oktober 2025',
                'tanggal' => '2025-09-01',
            ],
            [
                'judul' => 'Pengumpulan Proposal PKM',
                'isi' => 'Batas akhir pengumpulan proposal PKM adalah 20 September 2025.',
                'tanggal' => '2025-09-05',
            ],
            [
                'judul' =>'Penerimaan Beasiswa Yayasan', 
                'isi' => 'Mahasiswa dapat mengajukan beasiswa mulai 12 September 2025.', 
                'tanggal' => '2025-09-07',
            ],
            [
                'judul' => 'Jadwal Kuliah Pengganti', 
                'isi' => 'Kuliah Jaringan Komputer akan diganti tanggal 18 September 2025.', 
                'tanggal' => '2025-09-08',
            ],
            [
                'judul' => 'Pendaftaran Wisuda Periode November 2025',
                'isi' => 'Pendaftaran dibuka mulai 25 September 2025.',
                'tanggal' => '2025-09-09',
            ],
            [
                'judul' => 'Workshop AI dan Data Science', 
                'isi' => 'Akan dilaksanakan workshop AI pada 30 September 2025.',
                'tanggal' => '2025-09-09',
            ]
        ]);
    }
}
