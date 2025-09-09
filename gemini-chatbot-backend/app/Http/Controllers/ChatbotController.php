<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatbotController extends Controller
{
    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'session_id' => 'nullable|string'
        ]);

        $message = $request->input('message');
        $sessionId = $request->input('session_id', 'default');

        try {
            // 1. Cari di database kampus terlebih dahulu
            $dbResponse = $this->searchInDatabase($message);

            if ($dbResponse) {
                // Simpan ke chat history
                $this->saveToHistory($message, $dbResponse, $sessionId, 'database');
                return response()->json([
                    'response' => $dbResponse,
                    'source' => 'database'
                ]);
            }

            // 2. Jika tidak ditemukan di database, gunakan Gemini AI
            $geminiResponse = $this->getGeminiResponse($message);

            // Simpan ke chat history
            $this->saveToHistory($message, $geminiResponse, $sessionId, 'gemini');

            return response()->json([
                'response' => $geminiResponse,
                'source' => 'gemini'
            ]);
        } catch (\Exception $e) {
            Log::error('Chat error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    private function searchInDatabase($message)
    {
        $message = strtolower($message);

        // Cari di tabel pengumuman
        if (
            strpos($message, 'pengumuman') !== false ||
            strpos($message, 'pengumuman terbaru') !== false
        ) {

            $pengumuman = DB::table('pengumuman')
                ->orderBy('tanggal', 'desc')
                ->first();

            if ($pengumuman) {
                return "PENGUMUMAN TERBARU\n\n" .
                    "Judul: " . $pengumuman->judul .
                    "\nTanggal: " . $pengumuman->tanggal .
                    "\n\nIsi: " . substr($pengumuman->isi, 0, 200) .
                    (strlen($pengumuman->isi) > 200 ? "..." : "");
            } else {
                return "Maaf, tidak ada pengumuman terbaru yang ditemukan.";
            }
        }

        return null;
    }

    private function getGeminiResponse($message)
    {
        $apiKey = env('GEMINI_API_KEY');

        if (!$apiKey) {
            throw new \Exception('API key Gemini tidak ditemukan');
        }

        try {
            $response = Http::timeout(30)
                ->post('https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' . $apiKey, [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => "Jawab pertanyaan berikut dalam Bahasa Indonesia: " . $message]
                            ]
                        ]
                    ]
                ]);

            if ($response->failed()) {
                throw new \Exception('Gagal mendapatkan respons dari Gemini: ' . $response->body());
            }

            $data = $response->json();

            if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                return $data['candidates'][0]['content']['parts'][0]['text'];
            } else {
                throw new \Exception('Format respons Gemini tidak sesuai');
            }
        } catch (\Exception $e) {
            throw new \Exception('Gagal mendapatkan respons dari Gemini: ' . $e->getMessage());
        }
    }

    private function saveToHistory($userMessage, $botResponse, $sessionId, $source)
    {
        DB::table('chat_history')->insert([
            'user_message' => $userMessage,
            'bot_response' => $botResponse,
            'session_id' => $sessionId,
            'source' => $source,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    public function getHistory(Request $request)
    {
        $sessionId = $request->input('session_id', 'default');

        $history = DB::table('chat_history')
            ->where('session_id', $sessionId)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return response()->json($history);
    }

    public function testConnection()
    {
        return response()->json([
            'message' => 'Laravel 12.x backend is working!',
            'timestamp' => now()
        ]);
    }

    public function testDatabase()
    {
        try {
            DB::connection()->getPdo();
            $pengumumanCount = DB::table('pengumuman')->count();

            return response()->json([
                'message' => 'Database connection successful',
                'pengumuman_count' => $pengumumanCount,
                'database' => DB::connection()->getDatabaseName()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Database connection failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
