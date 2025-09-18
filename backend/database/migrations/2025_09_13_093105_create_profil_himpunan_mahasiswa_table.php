<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('profil_himpunan_mahasiswa', function (Blueprint $table) {
            $table->id('id_himpunan');
            $table->string('nama_himpunan', 100);
            $table->text('visi');
            $table->text('misi');
            $table->string('ketua_umum', 100);
            $table->string('periode', 20);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profil_himpunan_mahasiswa');
    }
};
