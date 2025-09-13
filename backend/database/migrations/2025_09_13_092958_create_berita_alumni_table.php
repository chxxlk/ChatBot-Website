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
        Schema::create('berita_alumni', function (Blueprint $table) {
            $table->id('id_berita');
            $table-> string('nama_alumni', 100);
            $table->string('judul_berita', 200);
            $table->text('isi');
            $table->date('tanggal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('berita_alumni');
    }
};
