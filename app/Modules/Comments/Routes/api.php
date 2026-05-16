<?php

use App\Modules\Comments\Controllers\CommentsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/ideas/{ideaId}/comments', [CommentsController::class, 'index']);
Route::middleware(['auth:sanctum'])->post('/ideas/{ideaId}/comments', [CommentsController::class, 'store']);
Route::middleware(['auth:sanctum'])->put('/comments/{commentId}/interactions', [CommentsController::class, 'updateInteraction']);
Route::middleware(['auth:sanctum'])->get('/my-comments', [CommentsController::class, 'myComments']);