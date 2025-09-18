<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\ChatbotInfoController;

// Route testing koneksi
Route::get('/test', [ChatbotController::class, 'testConnection']);
Route::get('/test-db', [ChatbotController::class, 'testDatabase']);

// Route chatbot
Route::post('/chat', [ChatbotController::class, 'chat']);
Route::get('/history', [ChatbotController::class, 'getHistory']);


// cek info dan welcome message
Route::get('/chatbot/info', [ChatbotInfoController::class, 'getInfo']);
Route::get('/chatbot/welcome', [ChatbotInfoController::class, 'getWelcomeMessage']);

// test routes untuk OpenRoutes
Route::get('/test/openrouter', [ChatbotController::class, 'testOpenRouter']);