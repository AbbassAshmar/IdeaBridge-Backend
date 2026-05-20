<?php

use App\Modules\Ideas\Controllers\IdeasController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'permission:list ideas'])->get('/ideas', [IdeasController::class, 'index']);
Route::middleware(['auth:sanctum', 'permission:list ideas'])->get('/ideas/{ideaId}', [IdeasController::class, 'show']);
Route::middleware(['auth:sanctum', 'permission:list ideas'])->get('/users/ideas', [IdeasController::class, 'userIndex']);
Route::middleware(['auth:sanctum', 'permission:list ideas'])->get('/users/developer-ideas', [IdeasController::class, 'developerPortfolio']);
Route::middleware(['auth:sanctum', 'permission:add idea'])->post('/ideas', [IdeasController::class, 'store']);
Route::middleware(['auth:sanctum'])->delete('/ideas/{ideaId}', [IdeasController::class, 'delete']);
Route::middleware(['auth:sanctum'])->put('/ideas/{ideaId}/interactions', [IdeasController::class, 'updateInteraction']);
Route::middleware(['auth:sanctum'])->post('/ideas/{ideaId}/take', [IdeasController::class, 'take']);
Route::middleware(['auth:sanctum'])->post('/ideas/{ideaId}/leave', [IdeasController::class, 'leave']);
Route::middleware(['auth:sanctum'])->post('/ideas/{ideaId}/complete', [IdeasController::class, 'complete']);
Route::middleware(['auth:sanctum'])->get('/ideas/{ideaId}/updates', [IdeasController::class, 'updates']);
Route::middleware(['auth:sanctum'])->post('/ideas/{ideaId}/updates', [IdeasController::class, 'storeUpdate']);
