<?php

namespace Tests;

use Tests\TestCase;
use App\Services\EmbeddingService;

class EmbeddingTest extends TestCase
{
    public function test_embeddinggemma_model()
    {
        $embeddingService = new EmbeddingService();
        
        // Test single embedding
        $embedding = $embeddingService->generateEmbedding('Test query untuk embedding');
        
        $this->assertNotNull($embedding, 'Embedding should not be null');
        $this->assertIsArray($embedding, 'Embedding should be an array');
        $this->assertGreaterThan(0, count($embedding), 'Embedding should have values');
        
        // Test similarity calculation
        $vector1 = $embeddingService->generateEmbedding('pengumuman terbaru');
        $vector2 = $embeddingService->generateEmbedding('informasi terbaru');
        
        if ($vector1 && $vector2) {
            $similarity = $embeddingService->cosineSimilarity($vector1, $vector2);
            $this->assertGreaterThan(0, $similarity, 'Similarity should be positive');
            $this->assertLessThanOrEqual(1, $similarity, 'Similarity should be <= 1');
        }
        
        // Test batch embeddings
        $texts = ['pengumuman kampus', 'informasi dosen'];
        $embeddings = $embeddingService->generateBatchEmbeddings($texts);
        
        $this->assertCount(3, $embeddings, 'Should return 3 embeddings for 3 texts');
    }
}