<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PengumumanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('pengumuman')->insert(
            [
                'judul' => 'Perkuliahan Online',
                'isi' => 'Perkuliahan online telah dibuka oleh PT Purabarutama, Perkuliahan akan dibuka dari tanggal 1 Juli s/d 31 Agustus 2025.',
                'file' => null,
                'kategori' => 'Pengumuman',
                'user_id' => 1,
            ],
            [
                'judul' => 'Perkuliahan Offline',
                'isi' => 'Perkuliahan offline telah dibuka oleh PT Purabarutama, Perkuliahan akan dibuka dari tanggal 1 Juli s/d 31 Agustus 2025.',
                'file' => null,
                'kategori' => 'Pengumuman',
                'user_id' => 1,
            ],
            [
                'judul' => 'Pendaftaran Yudisium',
                'isi' => 'Pendaftaran Yudisium telah dibuka sampai dengan 30 September 2025. Untuk informasi yang lebih lanjut silahkan hubungi Kaprodi',
                'file' => null,
                'kategori' => 'Pengumuman',
                'user_id' => 1,
            ]
        );
    }
}
