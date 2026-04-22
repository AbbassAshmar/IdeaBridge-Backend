<?php

namespace App\Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Requests\LoginRequest;
use App\Modules\Auth\Requests\RegisterRequest;
use App\Modules\Auth\Services\AuthService;
use App\Utils\ResponseHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(private readonly AuthService $authService)
    {
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $response = $this->authService->register($request, $request->validated());

        return response()->json(ResponseHelper::success($response), 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $response = $this->authService->login($request, $request->validated());

        return response()->json(ResponseHelper::success($response));
    }

    public function logout(Request $request): JsonResponse
    {
        $response = $this->authService->logout($request);

        return response()->json(ResponseHelper::success($response));
    }
}
