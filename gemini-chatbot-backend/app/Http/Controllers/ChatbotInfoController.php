<?php

namespace App\Http\Controllers;
use App\Services\RagService;
use Illuminate\Http\Request;

class ChatbotInfoController extends Controller
{
    protected $ragService;

    public function __construct(RagService $ragService)
    {
        $this->ragService = $ragService;
    }

    public function getInfo()
    {
        try {
            $info = $this->ragService->getChatbotInfo();

            return response()->json([
                'success' => true,
                'data' => $info,
                'message' => 'Informasi chatbot berhasil diambil'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Gagal mengambil informasi chatbot: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getWelcomeMessage()
    {
        $info = $this->ragService->getChatbotInfo();

        $welcomeMessage = "Halo! 👋 Saya {$info['name']}, {$info['role']} dari {$info['department']} di {$info['university']}. ";
        $welcomeMessage .= "Saya siap membantu Anda dengan informasi seputar kampus. Ada yang bisa saya bantu?";

        return response()->json([
            'success' => true,
            'data' => [
                'message' => $welcomeMessage,
                'identity' => $info
            ]
        ]);
    }
}
