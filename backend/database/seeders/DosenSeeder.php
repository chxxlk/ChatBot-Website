<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DosenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('dosen')->insert([
           'nama_lengkap' => 'Chris Lekpey',
           'keahlian_rekognisi' => 'Pemrograman Web',
           'email' => 'chris@gmail.com',
           'external_link' => 'https://chris.com',
           'photo' => null,
        ],
        [
            'nama_lengkap' => 'Stevanus',
            'keahlian_rekognisi' => 'Kecerdasan Buatan',
            'email' => 'stev@gmail.com',
            'external_link' => 'https://stevanus.com',
            'photo' => null,
        ]);
    }
}
