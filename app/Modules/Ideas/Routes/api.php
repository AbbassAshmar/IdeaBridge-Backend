<?php

use App\Modules\Ideas\Controllers\IdeasController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'permission:list ideas'])->get('/ideas', [IdeasController::class, 'index']);
Route::middleware(['auth:sanctum', 'permission:list ideas'])->get('/users/ideas', [IdeasController::class, 'userIndex']);
Route::middleware(['auth:sanctum', 'permission:create idea'])->post('/ideas', [IdeasController::class, 'store']);
