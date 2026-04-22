<?php

use App\Modules\Categories\Controllers\CategoriesController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'permission:list ideas'])->get('/categories', [CategoriesController::class, 'index']);