<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\SimpleChatbotController;
// Route testing koneksi
Route::get('/test', [ChatbotController::class, 'testConnection']);
// Route::get('/test', [SimpleChatbotController::class, 'test']);
Route::get('/test-db', [ChatbotController::class, 'testDatabase']);

// Route chatbot
Route::post('/chat', [ChatbotController::class, 'chat']);
// Route::post('/chat', [SimpleChatbotController::class, 'chat']);
Route::get('/history', [ChatbotController::class, 'getHistory']);
