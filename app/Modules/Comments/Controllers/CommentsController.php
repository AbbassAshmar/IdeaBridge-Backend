<?php

namespace App\Modules\Comments\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Comments\Requests\IndexIdeaCommentsRequest;
use App\Modules\Comments\Requests\StoreIdeaCommentRequest;
use App\Modules\Comments\Requests\UpdateCommentInteractionRequest;
use App\Modules\Comments\Resources\IdeaCommentResource;
use App\Modules\Comments\Services\CommentsService;
use App\Utils\ResponseHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Modules\Comments\Requests\IndexUserCommentsRequest;
use App\Modules\Comments\Resources\UserCommentResource;

class CommentsController extends Controller
{
    public function __construct(private readonly CommentsService $commentsService)
    {
    }

    public function index(int $ideaId, IndexIdeaCommentsRequest $request): JsonResponse
    {
        $response = $this->commentsService->listIdeaComments(
            $ideaId,
            $request->validated(),
            (int) $request->user()->id,
        );

        $response['data']['comments'] = IdeaCommentResource::collection(collect($response['data']['comments']))->resolve();

        return response()->json(ResponseHelper::success($response['data'], $response['meta']));
    }

    public function store(int $ideaId, StoreIdeaCommentRequest $request): JsonResponse
    {
        $response = $this->commentsService->createComment(
            $ideaId,
            (int) $request->user()->id,
            $request->validated(),
        );

        $response['comment'] = IdeaCommentResource::make($response['comment'])->resolve();

        return response()->json(ResponseHelper::success($response), 201);
    }

    public function updateInteraction(int $commentId, UpdateCommentInteractionRequest $request): JsonResponse
    {
        $response = $this->commentsService->updateInteraction(
            $commentId,
            (int) $request->user()->id,
            (string) $request->validated('state'),
        );

        return response()->json(ResponseHelper::success($response));
    }

    public function myComments(IndexUserCommentsRequest $request): JsonResponse
    {
        $response = $this->commentsService->listUserComments(
            (int) $request->user()->id,
            $request->validated(),
        );

        $response['data']['comments'] = UserCommentResource::collection(
            collect($response['data']['comments'])
        )->resolve();

        return response()->json(ResponseHelper::success($response['data'], $response['meta']));
    }
}
