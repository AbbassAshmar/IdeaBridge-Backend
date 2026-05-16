<?php

namespace App\Modules\Comments\Services;

use App\Exceptions\CommentRepositoryError;
use App\Exceptions\CommentsDomainError;
use App\Exceptions\IdeaRepositoryError;
use App\Modules\Comments\Repositories\CommentRepositoryInterface;
use App\Modules\Ideas\Repositories\IdeaRepositoryInterface;
use Illuminate\Support\Facades\Log;
use Throwable;

class CommentsService
{
    public function __construct(
        private readonly CommentRepositoryInterface $commentRepository,
        private readonly IdeaRepositoryInterface $ideaRepository
    ) {
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function listIdeaComments(int $ideaId, array $filters, int $authenticatedUserId): array
    {
        try {
            if (! $this->ideaRepository->existsById($ideaId)) {
                throw new CommentsDomainError('The requested idea was not found.', status: 404);
            }

            return $this->commentRepository->findIdeaComments($ideaId, $filters, $authenticatedUserId);
        } catch (CommentsDomainError $throwable) {
            throw $throwable;
        } catch (CommentRepositoryError|IdeaRepositoryError $throwable) {
            Log::error('Unable to load idea comments due to repository error.', [
                'idea_id' => $ideaId,
                'user_id' => $authenticatedUserId,
                'exception' => class_basename($throwable),
                'error' => $throwable->getMessage(),
            ]);

            throw (new CommentsDomainError('Unable to load comments.'))->causeBy($throwable);
        } catch (Throwable $throwable) {
            Log::error('Unable to load idea comments due to unexpected error.', [
                'idea_id' => $ideaId,
                'user_id' => $authenticatedUserId,
                'exception' => class_basename($throwable),
                'error' => $throwable->getMessage(),
            ]);

            throw (new CommentsDomainError('Unable to load comments.'))->causeBy($throwable);
        }
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function listUserComments(int $authenticatedUserId, array $filters): array
    {
        try {
            return $this->commentRepository->findUserComments($authenticatedUserId, $filters);
        } catch (CommentRepositoryError $throwable) {
            Log::error('Unable to load user comments due to repository error.', [
                'user_id'   => $authenticatedUserId,
                'exception' => class_basename($throwable),
                'error'     => $throwable->getMessage(),
            ]);

            throw (new CommentsDomainError('Unable to load comments.'))->causeBy($throwable);
        } catch (Throwable $throwable) {
            Log::error('Unable to load user comments due to unexpected error.', [
                'user_id'   => $authenticatedUserId,
                'exception' => class_basename($throwable),
                'error'     => $throwable->getMessage(),
            ]);

            throw (new CommentsDomainError('Unable to load comments.'))->causeBy($throwable);
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function createComment(int $ideaId, int $authenticatedUserId, array $payload): array
    {
        try {
            if (! $this->ideaRepository->existsById($ideaId)) {
                throw new CommentsDomainError('The requested idea was not found.', status: 404);
            }

            $parentId = isset($payload['parent_id']) ? (int) $payload['parent_id'] : null;
            $rootCommentId = null;

            if ($parentId) {
                $parentComment = $this->commentRepository->findCommentContextById($parentId);

                if (! $parentComment) {
                    throw new CommentsDomainError('The selected parent comment does not exist.');
                }

                if ((int) $parentComment['idea_id'] !== $ideaId) {
                    throw new CommentsDomainError('The selected parent comment does not belong to this idea.');
                }

                $rootCommentId = (int) ($parentComment['root_comment_id'] ?? $parentComment['id']);

                $rootComment = $this->commentRepository->findCommentContextById($rootCommentId);

                if (
                    ! $rootComment
                    || (int) $rootComment['idea_id'] !== $ideaId
                    || $rootComment['root_comment_id'] !== null
                ) {
                    throw new CommentsDomainError('The selected parent comment thread is invalid.');
                }
            }

            $comment = $this->commentRepository->createComment(
                $ideaId,
                $authenticatedUserId,
                (string) $payload['text'],
                $parentId,
                $rootCommentId
            );

            Log::info('Comment created successfully.', [
                'idea_id' => $ideaId,
                'user_id' => $authenticatedUserId,
                'parent_id' => $parentId,
                'root_comment_id' => $rootCommentId,
                'comment_id' => (int) ($comment['id'] ?? 0),
            ]);

            return [
                'comment' => $comment,
            ];
        } catch (CommentsDomainError $throwable) {
            throw $throwable;
        } catch (CommentRepositoryError|IdeaRepositoryError $throwable) {
            Log::error('Unable to create comment due to repository error.', [
                'idea_id' => $ideaId,
                'user_id' => $authenticatedUserId,
                'parent_id' => isset($payload['parent_id']) ? (int) $payload['parent_id'] : null,
                'exception' => class_basename($throwable),
                'error' => $throwable->getMessage(),
            ]);

            throw (new CommentsDomainError('Unable to create comment.'))->causeBy($throwable);
        } catch (Throwable $throwable) {
            Log::error('Unable to create comment due to unexpected error.', [
                'idea_id' => $ideaId,
                'user_id' => $authenticatedUserId,
                'parent_id' => isset($payload['parent_id']) ? (int) $payload['parent_id'] : null,
                'exception' => class_basename($throwable),
                'error' => $throwable->getMessage(),
            ]);

            throw (new CommentsDomainError('Unable to create comment.'))->causeBy($throwable);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function updateInteraction(int $commentId, int $authenticatedUserId, string $state): array
    {
        try {
            if (! $this->commentRepository->existsById($commentId)) {
                throw new CommentsDomainError('The requested comment was not found.', status: 404);
            }

            $response = [
                'interaction' => $this->commentRepository->setCommentInteraction($commentId, $authenticatedUserId, $state),
            ];

            Log::info('Comment interaction updated successfully.', [
                'comment_id' => $commentId,
                'user_id' => $authenticatedUserId,
                'state' => $state,
            ]);

            return $response;
        } catch (CommentsDomainError $throwable) {
            throw $throwable;
        } catch (CommentRepositoryError $throwable) {
            Log::error('Unable to update comment interaction due to repository error.', [
                'comment_id' => $commentId,
                'user_id' => $authenticatedUserId,
                'state' => $state,
                'exception' => class_basename($throwable),
                'error' => $throwable->getMessage(),
            ]);

            throw (new CommentsDomainError('Unable to update comment interaction.'))->causeBy($throwable);
        } catch (Throwable $throwable) {
            Log::error('Unable to update comment interaction due to unexpected error.', [
                'comment_id' => $commentId,
                'user_id' => $authenticatedUserId,
                'state' => $state,
                'exception' => class_basename($throwable),
                'error' => $throwable->getMessage(),
            ]);

            throw (new CommentsDomainError('Unable to update comment interaction.'))->causeBy($throwable);
        }
    }
}
