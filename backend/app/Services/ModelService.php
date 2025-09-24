<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ModelService
{
    private $apiKey;
    private $model;
    private $baseUrl;

    public function __construct()
    {
        $this->apiKey = env('OPENROUTER_API_KEY');
        $this->model = env('OPENROUTER_MODEL');
        $this->baseUrl = env('OPENROUTER_BASE_URL');

        if (!isset($this->apiKey) || !isset($this->baseUrl) || !isset($this->model)) {
            throw new \Exception('Missing HuggingFace API key, base URL, or model name.');
        }
    }

    public function generateResponse($prompt)
    {
        // Log::info('Kirim prompt ke OpenRouter', [
        //     'model' => $this->model,
        //     'prompt_preview' => substr($prompt, 0, 200) . '...' // biar log gak kepanjangan
        // ]);

        try {
            $response = Http::timeout(120)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                    'HTTP-Referer' => env('APP_URL'),
                    'X-Title' => 'S1 TI Chatbot'
                ])
                ->post($this->baseUrl . '/chat/completions', [
                    'model' => $this->model,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'Anda adalah Chris, asisten virtual dari Program Studi Teknologi Informasi UKSW. Jawablah dengan sopan dan informatif dalam Bahasa Indonesia.'
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt
                        ]
                    ],
                    'temperature' => 0.7,
                    // 'max_tokens' => 1024,
                    'stream' => false
                ]);
            // Log::info('Response dari OpenRouter', [
            //     'status' => $response->status(),
            //     'body'   => $response->body()
            // ]);

            if ($response->failed()) {
                Log::error('OpenRouter API Error: ' . $response->body());
                throw new \Exception('Gagal mendapatkan respons dari Model: ' . $response->status());
            }

            $data = $response->json();

            if (isset($data['choices'][0]['message']['content'])) {
                return $data['choices'][0]['message']['content'];
            } else {
                Log::error('OpenRouter response format: ' . json_encode($data));
                throw new \Exception('Format respons tidak sesuai dari OpenRouter');
            }
        } catch (\Exception $e) {
            Log::error('OpenRouter Service Error: ' . $e->getMessage());
            throw new \Exception('Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function getModels()
    {
        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                ])
                ->get($this->baseUrl . '/models');

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Failed to fetch models: ' . $e->getMessage());
            return [];
        }
    }
}
