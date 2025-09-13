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
        Schema::create('_profil_prodi', function (Blueprint $table) {
            $table->id('id_prodi');
            $table->text('visi');
            $table->text('misi');
            $table->string('akreditasi', 10);
            $table->string('ketua_prodi', 100);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('_profil_prodi');
    }
};
