<?php

namespace App\Modules\Ideas\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Ideas\Requests\CompleteIdeaRequest;
use App\Modules\Ideas\Requests\CreateIdeaRequest;
use App\Modules\Ideas\Requests\IndexIdeasRequest;
use App\Modules\Ideas\Requests\IndexIdeaUpdatesRequest;
use App\Modules\Ideas\Requests\LeaveIdeaRequest;
use App\Modules\Ideas\Requests\StoreIdeaUpdateRequest;
use App\Modules\Ideas\Requests\TakeIdeaRequest;
use App\Modules\Ideas\Requests\UpdateIdeaInteractionRequest;
use App\Modules\Ideas\Services\IdeasService;
use App\Utils\ResponseHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Modules\Ideas\Resources\IdeaResource;
use App\Modules\Ideas\Resources\IdeaUpdateResource;

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

    public function show(int $ideaId, Request $request): JsonResponse
    {
        $response = $this->ideasService->getIdeaById($ideaId, (int) $request->user()->id);

        $response['idea'] = IdeaResource::make($response['idea'])->resolve();

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

    public function take(int $ideaId, TakeIdeaRequest $request): JsonResponse
    {
        $response = $this->ideasService->takeIdea($ideaId, (int) $request->user()->id);
        $response['idea'] = IdeaResource::make($response['idea'])->resolve();

        return response()->json(ResponseHelper::success($response));
    }

    public function leave(int $ideaId, LeaveIdeaRequest $request): JsonResponse
    {
        $response = $this->ideasService->leaveIdea($ideaId, (int) $request->user()->id);
        $response['idea'] = IdeaResource::make($response['idea'])->resolve();

        return response()->json(ResponseHelper::success($response));
    }

    public function complete(int $ideaId, CompleteIdeaRequest $request): JsonResponse
    {
        $response = $this->ideasService->completeIdea($ideaId, (int) $request->user()->id);
        $response['idea'] = IdeaResource::make($response['idea'])->resolve();

        return response()->json(ResponseHelper::success($response));
    }

    public function delete(int $ideaId, Request $request): JsonResponse
    {
        $this->ideasService->deleteIdea($ideaId, (int) $request->user()->id);

        return response()->json(ResponseHelper::success());
    }

    public function developerPortfolio(Request $request): JsonResponse
    {
        $response = $this->ideasService->getDeveloperPortfolio((int) $request->user()->id);
        $response['currently_working_on'] = IdeaResource::collection(collect($response['currently_working_on']))->resolve();
        $response['completed_ideas'] = IdeaResource::collection(collect($response['completed_ideas']))->resolve();

        return response()->json(ResponseHelper::success($response));
    }

    public function updates(int $ideaId, IndexIdeaUpdatesRequest $request): JsonResponse
    {
        $response = $this->ideasService->listIdeaUpdates($ideaId, (int) $request->user()->id);
        $response['updates'] = IdeaUpdateResource::collection(collect($response['updates']))->resolve();

        return response()->json(ResponseHelper::success($response));
    }

    public function storeUpdate(int $ideaId, StoreIdeaUpdateRequest $request): JsonResponse
    {
        $response = $this->ideasService->createIdeaUpdate(
            $ideaId,
            (int) $request->user()->id,
            $request->validated(),
        );
        $response['update'] = IdeaUpdateResource::make($response['update'])->resolve();

        return response()->json(ResponseHelper::success($response), 201);
    }
}
