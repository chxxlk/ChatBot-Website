<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ChatbotController extends Controller
{
    /**
     * Menangani query dari user dan mengembalikan jawaban yang sesuai.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
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
            $RageService = new \App\Services\RagService();
            $response = $RageService->queryWithContext($message);
            // Simpan ke chat history
            $this->saveToHistory($message, $response, $sessionId, 'rag');

            return $this->addCorsHeaders(response()->json([
                'response' => $response,
                'source' => 'rag'
            ]));
        } catch (\Exception $e) {
            Log::error('Chat error: ' . $e->getMessage());
        }
    }

    /**
     * Semantic search test endpoint.
     * 
     * This endpoint is used to test semantic search functionality.
     * It takes a query as an input and returns the relevant data found
     * in the database.
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function semanticSearchTest(Request $request)
    {
        try {
            $query = $request->input('query', '');
            $ragService = new \App\Services\RagService();

            // Test semantic search
            $context = $ragService->getSemanticRelevantData($query);

            return response()->json([
                'query' => $query,
                'context' => $context,
                'success' => true
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Semantic search test failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Adds CORS headers to a response.
     * 
     * @param \Illuminate\Http\Response $response
     * @return \Illuminate\Http\Response
     */
    private function addCorsHeaders($response)
    {
        return $response
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With')
            ->header('Access-Control-Allow-Credentials', 'true');
    }

    /**
     * Cleans the response format by removing asterisks, replacing bullet points with numbering,
     * removing multiple newlines, and formatting dates consistently.
     *
     * @param string $response The response to clean
     * @return string The cleaned response
     */

    /**
     * Save a chat history entry to the database.
     *
     * @param string $userMessage The message sent by the user
     * @param string $botResponse The response sent by the bot
     * @param string $sessionId The session ID of the user
     * @param string $source The source of the response (e.g. 'database', 'gemini', 'rag', 'system')
     */
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

    /**
     * Returns the chat history of a user.
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
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

    /**
     * Test the connection to the Laravel 12.x backend.
     * 
     * This route is used to test if the connection to the Laravel 12.x backend is working.
     * 
     * @return \Illuminate\Http\JsonResponse
     */

    public function testConnection()
    {
        return response()->json([
            'message' => 'Laravel 12.x backend is working!',
            'timestamp' => now()
        ]);
    }

    /**
     * Tests the connection to the database.
     * 
     * This route is used to test if the connection to the database is working.
     * 
     * @return \Illuminate\Http\JsonResponse
     * 
     * @throws \Exception
     */
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
