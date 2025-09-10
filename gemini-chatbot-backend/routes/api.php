<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatbotController;
// Route testing koneksi
Route::get('/test', [ChatbotController::class, 'testConnection']);
Route::get('/test-db', [ChatbotController::class, 'testDatabase']);

// Route chatbot
Route::post('/chat', [ChatbotController::class, 'chat']);
Route::get('/history', [ChatbotController::class, 'getHistory']);
