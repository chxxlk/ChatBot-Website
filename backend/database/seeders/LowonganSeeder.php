<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LowonganSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('lowongan')->insert([
            'judul' => 'Lowongan Magang',
            'deskripsi' => 'Lowongan Magang telah dibuka oleh PT Purabarutama, Lowongan akan dibuka dari tanggal 1 Juli s/d 31 Agustus 2025.',
            'file' => null,
            'link_pendaftaran' => 'https://forms.gle/7w7b7w7b7w7b7w7b7',
            'user_id' => 1
        ],
        [
            'judul' => 'Lowongan Asisten Dosen Matakuliah Jaringan Komputer',
            'deskripsi' => 'Lowongan Asisten Dosen Matakuliah Jaringan Komputer telah dibuka oleh PT Purabarutama, Lowongan akan dibuka dari tanggal 1 Juli s/d 31 Agustus 2025.',
            'file' => null,
            'link_pendaftaran' => 'https://forms.gle/7w7b7w7b7w7b7w7b7',
            'user_id' => 1
        ],
        [
            'judul' => 'Lowongan Asisten Dosen Matakuliah Sistem Operasi',
            'deskripsi' => 'Lowongan Asisten Dosen Matakuliah Sistem Operasi telah dibuka oleh PT Purabarutama, Lowongan akan dibuka dari tanggal 1 Juli s/d 31 Agustus 2025.',
            'file' => null,
            'link_pendaftaran' => 'https://forms.gle/7w7b7w7b7w7b7w7b7',
            'user_id' => 1
        ]);
    }
}
