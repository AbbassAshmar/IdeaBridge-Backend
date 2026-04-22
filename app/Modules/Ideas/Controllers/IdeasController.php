<?php

namespace App\Modules\Ideas\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Ideas\Requests\CreateIdeaRequest;
use App\Modules\Ideas\Requests\IndexIdeasRequest;
use App\Modules\Ideas\Services\IdeasService;
use App\Utils\ResponseHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IdeasController extends Controller
{
    public function __construct(private readonly IdeasService $ideasService)
    {
    }

    public function index(IndexIdeasRequest $request): JsonResponse
    {
        $response = $this->ideasService->listIdeas($request->validated());

        return response()->json(ResponseHelper::success($response['data'], $response['meta']));
    }

    public function userIndex(Request $request): JsonResponse
    {
        $response = $this->ideasService->listIdeasForUser((int) $request->user()->id);

        return response()->json(ResponseHelper::success($response));
    }

    public function store(CreateIdeaRequest $request): JsonResponse
    {
        $response = $this->ideasService->createIdea((int) $request->user()->id, $request->validated());

        return response()->json(ResponseHelper::success($response), 201);
    }
}
