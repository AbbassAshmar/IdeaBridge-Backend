<?php

namespace App\Modules\Ideas\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\IdeaResource;
use App\Modules\Ideas\Requests\CreateIdeaRequest;
use App\Modules\Ideas\Requests\IndexIdeasRequest;
use App\Modules\Ideas\Requests\UpdateIdeaInteractionRequest;
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
        $response = $this->ideasService->listIdeas($request->validated(), (int) $request->user()->id);

        $response['data']['ideas'] = IdeaResource::collection(collect($response['data']['ideas']))->resolve();

        return response()->json(ResponseHelper::success($response['data'], $response['meta']));
    }

    public function userIndex(Request $request): JsonResponse
    {
        $response = $this->ideasService->listIdeasForUser((int) $request->user()->id);

        $response['ideas'] = IdeaResource::collection(collect($response['ideas']))->resolve();

        return response()->json(ResponseHelper::success($response));
    }

    public function store(CreateIdeaRequest $request): JsonResponse
    {
        $response = $this->ideasService->createIdea((int) $request->user()->id, $request->validated());

        $response['idea'] = IdeaResource::make($response['idea'])->resolve();

        return response()->json(ResponseHelper::success($response), 201);
    }

    public function updateInteraction(int $ideaId, UpdateIdeaInteractionRequest $request): JsonResponse
    {
        $response = $this->ideasService->updateInteraction(
            $ideaId,
            (int) $request->user()->id,
            (string) $request->validated('state')
        );

        return response()->json(ResponseHelper::success($response));
    }
}
