<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\ChatbotInfoController;

Route::get('/test', [ChatbotController::class, 'testConnection']);

// versi non-stream chat (POST)
Route::post('/chat', [ChatbotController::class, 'chat']);

// versi streaming chat (GET) — supaya EventSource di browser bisa pakai GET
Route::get('/chat/stream', [ChatbotController::class, 'chatStream']);

// Histori jika kamu punya
Route::get('/history', [ChatbotController::class, 'getHistory']);

// Info / welcome
Route::get('/chatbot/info', [ChatbotInfoController::class, 'getInfo']);
Route::get('/chatbot/welcome', [ChatbotInfoController::class, 'getWelcomeMessage']);

// Tes OpenRouter, Huggingface dam db
Route::get('/test/openrouter', [ChatbotController::class, 'testOpenRouter']);
Route::get('/test/hf', [ChatbotController::class, 'testEmbedding']);
Route::get('/test/db', [ChatbotController::class, 'testDatabase']);
