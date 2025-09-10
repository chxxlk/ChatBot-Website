<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ChatbotController extends Controller
{
    public function chat(Request $request)
    {
        if ($request->isMethod('OPTIONS')) {
            return $this->addCorsHeaders(response()->json([]));
        }

        $request->validate([
            'message' => 'required|string',
            'session_id' => 'nullable|string'
        ]);

        $message = $request->input('message');
        $sessionId = $request->input('session_id', 'default');

        try {
            // Gunakan Advanced RAG service
            $ragService = new \App\Services\AdvancedRagService();
            $response = $ragService->smartQuery($message);

            // Simpan ke chat history
            $this->saveToHistory($message, $response, $sessionId, 'advanced_rag');

            return $this->addCorsHeaders(response()->json([
                'response' => $response,
                'source' => 'advanced_rag'
            ]));
        } catch (\Exception $e) {
            Log::error('Chat error: ' . $e->getMessage());

            // Fallback ke basic response
            return $this->addCorsHeaders(response()->json([
                'response' => 'Maaf, saya sedang mengalami gangguan teknis. Silakan coba lagi nanti. ',
                'source' => 'fallback'
            ]));
        }
    }

    private function addCorsHeaders($response)
    {
        return $response
            ->header('Access-Control-Allow-Origin', 'http://localhost:5173')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With')
            ->header('Access-Control-Allow-Credentials', 'true');
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
