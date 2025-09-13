<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BeritaAlumniSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('berita_alumni')->insert([
            [
                'nama_alumni' => 'Andi Pratama',
                'judul_berita' => 'Alumni TI Menjadi CTO Startup',
                'isi' => 'Andi kini menjabat sebagai CTO di startup teknologi finansial',
                'tanggal' => '2025-07-10'
            ],
            [
                'nama_alumni' => 'Rina Santoso',
                'judul_berita' => 'Rina Raih Gelar Master di Jepang',
                'isi' => 'Rina berhasil menyelesaikan studi S2 di Tokyo Institute of Technology.',
                'tanggal' => '2025-06-22'
            ],
            [
                'nama_alumni' => 'Budi Wijaya',
                'judul_berita' => 'Alumni Budi Mendapatkan Penghargaan Inovasi',
                'isi' => 'Budi menerima penghargaan inovasi dari Kemenristek.',
                'tanggal' => '2025-05-15'
            ],
            [
                'nama_alumni' => 'Siti Aminah',
                'judul_berita' => 'Karir Gemilang di Google',
                'isi' => 'Siti bekerja sebagai software engineer di Google',
                'tanggal' => '2025-04-11'
            ],
            [
                'nama_alumni' => 'David Kusuma',
                'judul_berita' => 'David Membuka Perusahaan Konsultan IT',
                'isi' => 'Perusahaan konsultan IT milik David berkembang pesat.',
                'tanggal' => '2025-03-30'
            ],
            [
                'nama_alumni' => 'Maya Anggraini',
                'judul_berita' => 'Maya Menjadi Dosen di UNS',
                'isi' => 'Maya bergabung menjadi dosen tetap di UNS',
                'tanggal' => '2025-02-17'
            ],
        ]);
    }
}
