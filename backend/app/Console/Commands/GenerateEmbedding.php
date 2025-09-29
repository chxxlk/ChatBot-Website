<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\EmbeddingService;
use App\Models\Embedding;
use Illuminate\Support\Facades\DB;

class GenerateEmbedding extends Command
{
    protected $signature = 'embeddings:generate';
    protected $description = 'Generate embeddings untuk tabel tertentu';

    public function handle(EmbeddingService $service)
    {
        $table = $this->argument('pengumuman', 'lowongan', 'dosen');
        $rows = DB::table($table)->get();

        foreach ($rows as $row) {
            $text = implode(' ', array_filter((array)$row)); // gabung semua kolom jadi satu teks
            $vec = $service->generateEmbedding($text);

            if ($vec) {
                Embedding::updateOrCreate(
                    ['table_name' => $table, 'row_id' => $row->id],
                    ['vector' => $vec]
                );
                $this->info("✔️ Row {$row->id} processed");
            }
        }

        $this->info("✅ Embeddings generated for table {$table}");
    }
}
