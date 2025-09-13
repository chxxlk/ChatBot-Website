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
        Schema::create('lowongan_asisten_dosen', function (Blueprint $table) {
            $table->id('id_lowongan');
            $table->string('matakuliah', 100);
            $table->text('kualifikasi');
            $table->date('deadline');
            $table->string('kontak', 100);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lowongan_asisten_dosen');
    }
};
