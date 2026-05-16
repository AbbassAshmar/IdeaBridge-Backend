<?php

namespace App\Modules\Comments\Repositories;

use App\Exceptions\CommentRepositoryError;
use App\Models\CommentInteraction;
use App\Models\IdeaComment;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Throwable;

class EloquentCommentRepository implements CommentRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function findIdeaComments(int $ideaId, array $filters, int $authenticatedUserId): array
    {
        try {
            $page = max(1, (int) ($filters['page'] ?? 1));
            $limit = max(1, min(100, (int) ($filters['limit'] ?? 10)));

            $paginator = $this->baseCommentsQuery($authenticatedUserId)
                ->where('idea_id', $ideaId)
                ->whereNull('root_comment_id')
                ->with([
                    'user',
                    'replies' => function ($query) use ($authenticatedUserId): void {
                        $query
                            ->with(['user', 'parent.user'])
                            ->withCount([
                                'interactions as upvotes_count' => fn ($interactionQuery) => $interactionQuery->where('state', 'upvote'),
                                'interactions as downvotes_count' => fn ($interactionQuery) => $interactionQuery->where('state', 'downvote'),
                            ])
                            ->addSelect([
                                'auth_user_interaction' => CommentInteraction::query()
                                    ->select('state')
                                    ->whereColumn('comment_id', 'idea_comments.id')
                                    ->where('user_id', $authenticatedUserId)
                                    ->limit(1),
                            ])
                            ->orderBy('created_at', 'asc');
                    },
                ])
                ->withCount('replies')
                ->orderByDesc('created_at')
                ->paginate($limit, ['*'], 'page', $page);

            return [
                'data' => [
                    'comments' => $paginator->getCollection()
                        ->map(fn (IdeaComment $comment): array => $this->mapComment($comment, true))
                        ->values()
                        ->all(),
                ],
                'meta' => [
                    'pagination' => [
                        'total_count' => $paginator->total(),
                        'page' => $paginator->currentPage(),
                        'limit' => $paginator->perPage(),
                        'total_pages' => $paginator->lastPage(),
                    ],
                ],
            ];
        } catch (Throwable $throwable) {
            Log::error('Failed to load comments list from repository.', [
                'idea_id' => $ideaId,
                'filters' => [
                    'page' => (int) ($filters['page'] ?? 1),
                    'limit' => (int) ($filters['limit'] ?? 10),
                ],
                'authenticated_user_id' => $authenticatedUserId,
                'exception' => class_basename($throwable),
                'error' => $throwable->getMessage(),
            ]);

            throw (new CommentRepositoryError('Unable to load comments.'))->causeBy($throwable);
        }
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function findUserComments(int $userId, array $filters): array
    {
        try {
            $page  = max(1, (int) ($filters['page']  ?? 1));
            $limit = max(1, min(100, (int) ($filters['limit'] ?? 10)));

            $paginator = IdeaComment::query()
                ->where('user_id', $userId)
                ->with(['user', 'parent.user'])
                ->withCount([
                    'interactions as upvotes_count'   => fn ($q) => $q->where('state', 'upvote'),
                    'interactions as downvotes_count' => fn ($q) => $q->where('state', 'downvote'),
                ])
                ->addSelect([
                    'auth_user_interaction' => CommentInteraction::query()
                        ->select('state')
                        ->whereColumn('comment_id', 'idea_comments.id')
                        ->where('user_id', $userId)
                        ->limit(1),
                ])
                ->orderByDesc('created_at')
                ->paginate($limit, ['*'], 'page', $page);

            return [
                'data' => [
                    'comments' => $paginator->getCollection()
                        ->map(fn (IdeaComment $comment): array => $this->mapUserComment($comment))
                        ->values()
                        ->all(),
                ],
                'meta' => [
                    'pagination' => [
                        'total_count' => $paginator->total(),
                        'page'        => $paginator->currentPage(),
                        'limit'       => $paginator->perPage(),
                        'total_pages' => $paginator->lastPage(),
                    ],
                ],
            ];
        } catch (Throwable $throwable) {
            Log::error('Failed to load user comments from repository.', [
                'user_id'   => $userId,
                'filters'   => ['page' => $page ?? 1, 'limit' => $limit ?? 10],
                'exception' => class_basename($throwable),
                'error'     => $throwable->getMessage(),
            ]);

            throw (new CommentRepositoryError('Unable to load comments.'))->causeBy($throwable);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function mapUserComment(IdeaComment $comment): array
    {
        $mapped = [
            'id'                   => (int) $comment->id,
            'idea_id'              => (int) $comment->idea_id,
            'text'                 => (string) $comment->text,
            'created_at'           => $comment->created_at?->toISOString(),
            'user'                 => $comment->user ? $this->mapUser($comment->user) : null,
            'upvotes_count'        => (int) ($comment->upvotes_count   ?? 0),
            'downvotes_count'      => (int) ($comment->downvotes_count ?? 0),
            'auth_user_interaction'=> $comment->auth_user_interaction ?: null,
        ];

        if ($comment->parent) {
            $mapped['parent'] = [
                'id'   => (int) $comment->parent->id,
                'user' => $comment->parent->user ? $this->mapUser($comment->parent->user) : null,
            ];
        }

        return $mapped;
    }

    public function findCommentContextById(int $commentId): ?array
    {
        try {
            $comment = IdeaComment::query()
                ->select(['id', 'idea_id', 'root_comment_id', 'parent_id', 'user_id'])
                ->find($commentId);

            if (! $comment) {
                return null;
            }

            return [
                'id' => (int) $comment->id,
                'idea_id' => (int) $comment->idea_id,
                'root_comment_id' => isset($comment->root_comment_id) ? (int) $comment->root_comment_id : null,
                'parent_id' => isset($comment->parent_id) ? (int) $comment->parent_id : null,
                'user_id' => (int) $comment->user_id,
            ];
        } catch (Throwable $throwable) {
            Log::error('Failed to load comment context from repository.', [
                'comment_id' => $commentId,
                'exception' => class_basename($throwable),
                'error' => $throwable->getMessage(),
            ]);

            throw (new CommentRepositoryError('Unable to load the requested comment.'))->causeBy($throwable);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function createComment(
        int $ideaId,
        int $userId,
        string $text,
        ?int $parentId,
        ?int $rootCommentId
    ): array {
        try {
            $comment = IdeaComment::query()->create([
                'idea_id' => $ideaId,
                'user_id' => $userId,
                'text' => $text,
                'parent_id' => $parentId,
                'root_comment_id' => $rootCommentId,
            ]);

            $comment = $this->baseCommentsQuery($userId)
                ->with(['user', 'parent.user'])
                ->whereKey($comment->id)
                ->firstOrFail();

            return $this->mapComment($comment);
        } catch (Throwable $throwable) {
            Log::error('Failed to create comment in repository.', [
                'idea_id' => $ideaId,
                'user_id' => $userId,
                'parent_id' => $parentId,
                'root_comment_id' => $rootCommentId,
                'exception' => class_basename($throwable),
                'error' => $throwable->getMessage(),
            ]);

            throw (new CommentRepositoryError('Unable to create the comment.'))->causeBy($throwable);
        }
    }

    public function existsById(int $commentId): bool
    {
        try {
            return IdeaComment::query()->whereKey($commentId)->exists();
        } catch (Throwable $throwable) {
            Log::error('Failed to check comment existence in repository.', [
                'comment_id' => $commentId,
                'exception' => class_basename($throwable),
                'error' => $throwable->getMessage(),
            ]);

            throw (new CommentRepositoryError('Unable to validate the requested comment.'))->causeBy($throwable);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function setCommentInteraction(int $commentId, int $userId, string $state): array
    {
        try {
            if ($state === 'neutral') {
                CommentInteraction::query()
                    ->where('comment_id', $commentId)
                    ->where('user_id', $userId)
                    ->delete();
            } else {
                CommentInteraction::query()->updateOrCreate(
                    [
                        'comment_id' => $commentId,
                        'user_id' => $userId,
                    ],
                    [
                        'state' => $state,
                    ]
                );
            }

            $comment = $this->baseCommentsQuery($userId)
                ->whereKey($commentId)
                ->firstOrFail();

            return [
                'comment_id' => (int) $comment->id,
                'user_id' => $userId,
                'auth_user_interaction' => $comment->auth_user_interaction ?: null,
                'upvotes_count' => (int) ($comment->upvotes_count ?? 0),
                'downvotes_count' => (int) ($comment->downvotes_count ?? 0),
            ];
        } catch (Throwable $throwable) {
            Log::error('Failed to set comment interaction in repository.', [
                'comment_id' => $commentId,
                'user_id' => $userId,
                'state' => $state,
                'exception' => class_basename($throwable),
                'error' => $throwable->getMessage(),
            ]);

            throw (new CommentRepositoryError('Unable to update comment interaction.'))->causeBy($throwable);
        }
    }

    private function baseCommentsQuery(int $authenticatedUserId)
    {
        return IdeaComment::query()
            ->withCount([
                'interactions as upvotes_count' => fn ($query) => $query->where('state', 'upvote'),
                'interactions as downvotes_count' => fn ($query) => $query->where('state', 'downvote'),
            ])
            ->addSelect([
                'auth_user_interaction' => CommentInteraction::query()
                    ->select('state')
                    ->whereColumn('comment_id', 'idea_comments.id')
                    ->where('user_id', $authenticatedUserId)
                    ->limit(1),
            ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function mapComment(IdeaComment $comment, bool $includeReplies = false): array
    {
        $mapped = [
            'id' => (int) $comment->id,
            'text' => (string) $comment->text,
            'created_at' => $comment->created_at?->toISOString(),
            'user' => $comment->user ? $this->mapUser($comment->user) : null,
            'upvotes_count' => (int) ($comment->upvotes_count ?? 0),
            'downvotes_count' => (int) ($comment->downvotes_count ?? 0),
            'auth_user_interaction' => $comment->auth_user_interaction ?: null,
        ];

        if ($comment->parent) {
            $mapped['parent'] = [
                'id' => (int) $comment->parent->id,
                'user' => $comment->parent->user ? $this->mapUser($comment->parent->user) : null,
            ];
        }

        if ($includeReplies) {
            $mapped['replies_count'] = (int) ($comment->replies_count ?? 0);
            $mapped['replies'] = $comment->relationLoaded('replies')
                ? $this->mapReplies($comment->replies)
                : [];
        }

        return $mapped;
    }

    /**
     * @param  Collection<int, IdeaComment>  $replies
     * @return array<int, array<string, mixed>>
     */
    private function mapReplies(Collection $replies): array
    {
        return $replies
            ->map(fn (IdeaComment $reply): array => $this->mapComment($reply))
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function mapUser(User $user): array
    {
        return [
            'id' => (int) $user->id,
            'username' => $user->username,
            'email' => $user->email,
        ];
    }
}
