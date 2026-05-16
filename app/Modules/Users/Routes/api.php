<?php

use App\Modules\Users\Controllers\UsersController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/users/profile', [UsersController::class, 'me']);
Route::middleware('auth:sanctum')->patch('/users/profile', [UsersController::class, 'updateProfile']);
