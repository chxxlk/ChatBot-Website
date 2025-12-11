<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // pastikan extension pgvector aktif
        DB::statement('CREATE EXTENSION IF NOT EXISTS vector');
        Schema::create('embeddings', function (Blueprint $table) {
            $table->id();
            $table->string('table_name');
            $table->unsignedBigInteger('row_id');
            $table->vector('vector', 4096);
            $table->timestamps();

            $table->unique(['table_name', 'row_id']);
            $table->index('table_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('embeddings');
    }
};
