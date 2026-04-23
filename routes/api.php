<?php

use App\Utils\ResponseHelper;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'web',
])->group(function () {
    Route::prefix('auth')->group(base_path('app/Modules/Auth/Routes/api.php'));
    Route::group([], base_path('app/Modules/Ideas/Routes/api.php'));
    Route::group([], base_path('app/Modules/Categories/Routes/api.php'));

    Route::middleware('auth:sanctum')->get('/auth/user', function () {
        return response()->json(ResponseHelper::success([
            'user' => request()->user(),
        ]));
    });
});

