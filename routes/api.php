<?php

use App\Modules\Users\Controllers\UsersController;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'web',
])->group(function () {
    Route::prefix('auth')->group(base_path('app/Modules/Auth/Routes/api.php'));
    Route::group([], base_path('app/Modules/Ideas/Routes/api.php'));
    Route::group([], base_path('app/Modules/Comments/Routes/api.php'));
    Route::group([], base_path('app/Modules/Categories/Routes/api.php'));
    Route::group([], base_path('app/Modules/Users/Routes/api.php'));

    Route::middleware('auth:sanctum')->get('/auth/user', [UsersController::class, 'me']);
});
