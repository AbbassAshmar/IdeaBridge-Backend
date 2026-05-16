<?php

namespace App\Modules\Users\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Users\Requests\UpdateProfileRequest;
use App\Modules\Users\Services\UsersService;
use App\Utils\ResponseHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    public function __construct(private readonly UsersService $usersService)
    {
    }

    public function me(Request $request): JsonResponse
    {
        $response = $this->usersService->getAuthenticatedUser((int) $request->user()->id);

        return response()->json(ResponseHelper::success($response));
    }

    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $response = $this->usersService->updateProfile(
            (int) $request->user()->id,
            $request->validated(),
        );

        return response()->json(ResponseHelper::success($response));
    }
}
