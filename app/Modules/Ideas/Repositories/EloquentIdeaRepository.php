<?php

namespace App\Modules\Ideas\Repositories;

use App\Exceptions\IdeaRepositoryError;
use App\Models\Idea;
use App\Models\IdeaInteraction;
use App\Models\IdeaUpdate;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Throwable;

class EloquentIdeaRepository implements IdeaRepositoryInterface
{
    public function findIdeasByOwnerId(int $ownerUserId, int $authenticatedUserId): array
    {
        try {
            $isDeveloper = $this->resolveIsDeveloper($authenticatedUserId);

            return $this->baseIdeasQuery($authenticatedUserId)
                ->where('user_id', $ownerUserId)
                ->orderByDesc('created_at')
                ->get()
                ->map(fn (Idea $idea): array => $this->mapIdea($idea, $authenticatedUserId, $isDeveloper))
                ->all();
        } catch (Throwable $throwable) {
            Log::error('Failed to load ideas for owner from repository.', [
                'owner_user_id' => $ownerUserId,
                'authenticated_user_id' => $authenticatedUserId,
                'exception' => class_basename($throwable),
                'error' => $throwable->getMessage(),
            ]);

            throw (new IdeaRepositoryError('Unable to load ideas for the requested user.'))->causeBy($throwable);
        }
    }

    public function findIdeaById(int $ideaId, int $authenticatedUserId): ?array
    {
        try {
            $isDeveloper = $this->resolveIsDeveloper($authenticatedUserId);

            $idea = $this->baseIdeasQuery($authenticatedUserId)
                ->whereKey($ideaId)
                ->first();

            return $idea ? $this->mapIdea($idea, $authenticatedUserId, $isDeveloper) : null;
        } catch (Throwable $throwable) {
            Log::error('Failed to load idea by ID from repository.', [
                'idea_id' => $ideaId,
                'authenticated_user_id' => $authenticatedUserId,
                'exception' => class_basename($throwable),
                'error' => $throwable->getMessage(),
            ]);

            throw (new IdeaRepositoryError('Unable to load the requested idea.'))->causeBy($throwable);
        }
    }

    public function findIdeas(array $filters, int $authenticatedUserId): array
    {
        try {
            $page = max(1, (int) ($filters['page'] ?? 1));
            $limit = max(1, min(100, (int) ($filters['limit'] ?? 15)));
            $search = trim((string) ($filters['q'] ?? ''));
            $sort = strtolower((string) ($filters['sort'] ?? 'desc'));
            $isDeveloper = $this->resolveIsDeveloper($authenticatedUserId);

            $query = $this->baseIdeasQuery($authenticatedUserId);

            if ($search !== '') {
                $query->where(function ($builder) use ($search): void {
                    $builder->where('title', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%');
                });
            }

            $query->orderBy('created_at', $sort === 'asc' ? 'asc' : 'desc');

            $paginator = $query->paginate($limit, ['*'], 'page', $page);

            return [
                'data' => [
                    'ideas' => $paginator->getCollection()
                        ->map(fn (Idea $idea): array => $this->mapIdea($idea, $authenticatedUserId, $isDeveloper))
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
            Log::error('Failed to load ideas list from repository.', [
                'filters' => [
                    'page' => (int) ($filters['page'] ?? 1),
                    'limit' => (int) ($filters['limit'] ?? 15),
                    'q' => (string) ($filters['q'] ?? ''),
                    'sort' => (string) ($filters['sort'] ?? 'desc'),
                ],
                'authenticated_user_id' => $authenticatedUserId,
                'exception' => class_basename($throwable),
                'error' => $throwable->getMessage(),
            ]);

            throw (new IdeaRepositoryError('Unable to load ideas.'))->causeBy($throwable);
        }
    }

    public function createIdea(array $ideaData): array
    {
        try {
            $idea = Idea::query()->create([
                'user_id' => (int) $ideaData['user_id'],
                'category_id' => (int) $ideaData['category_id'],
                'title' => (string) $ideaData['title'],
                'description' => (string) $ideaData['description'],
                'taken_by_user_id' => null,
                'status' => 'open',
            ]);

            $authenticatedUserId = (int) $ideaData['user_id'];
            $idea = $this->baseIdeasQuery($authenticatedUserId)
                ->whereKey($idea->id)
                ->firstOrFail();

            return $this->mapIdea($idea, $authenticatedUserId, $this->resolveIsDeveloper($authenticatedUserId));
        } catch (Throwable $throwable) {
            Log::error('Failed to create idea in repository.', [
                'user_id' => (int) ($ideaData['user_id'] ?? 0),
                'category_id' => (int) ($ideaData['category_id'] ?? 0),
                'exception' => class_basename($throwable),
                'error' => $throwable->getMessage(),
            ]);

            throw (new IdeaRepositoryError('Unable to create the idea.'))->causeBy($throwable);
        }
    }

    public function existsById(int $ideaId): bool
    {
        try {
            return Idea::query()->whereKey($ideaId)->exists();
        } catch (Throwable $throwable) {
            Log::error('Failed to check idea existence in repository.', [
                'idea_id' => $ideaId,
                'exception' => class_basename($throwable),
                'error' => $throwable->getMessage(),
            ]);

            throw (new IdeaRepositoryError('Unable to validate the requested idea.'))->causeBy($throwable);
        }
    }

    public function setIdeaInteraction(int $ideaId, int $userId, string $state): array
    {
        try {
            if ($state === 'neutral') {
                IdeaInteraction::query()
                    ->where('idea_id', $ideaId)
                    ->where('user_id', $userId)
                    ->delete();
            } else {
                IdeaInteraction::query()->updateOrCreate(
                    [
                        'idea_id' => $ideaId,
                        'user_id' => $userId,
                    ],
                    [
                        'state' => $state,
                    ]
                );
            }

            $idea = $this->baseIdeasQuery($userId)
                ->whereKey($ideaId)
                ->firstOrFail();

            return [
                'idea_id' => (int) $idea->id,
                'user_id' => $userId,
                'user_vote' => (string) ($idea->user_vote ?? 'neutral'),
                'upvotes_count' => (int) ($idea->upvotes_count ?? 0),
                'downvotes_count' => (int) ($idea->downvotes_count ?? 0),
            ];
        } catch (Throwable $throwable) {
            Log::error('Failed to set idea interaction in repository.', [
                'idea_id' => $ideaId,
                'user_id' => $userId,
                'state' => $state,
                'exception' => class_basename($throwable),
                'error' => $throwable->getMessage(),
            ]);

            throw (new IdeaRepositoryError('Unable to update idea interaction.'))->causeBy($throwable);
        }
    }

    public function updateIdeaWorkflow(int $ideaId, ?int $takenByUserId, string $status, int $authenticatedUserId): array
    {
        try {
            $idea = Idea::query()->findOrFail($ideaId);
            $idea->taken_by_user_id = $takenByUserId;
            $idea->status = $status;
            $idea->save();
            $idea->refresh();

            $idea = $this->baseIdeasQuery($authenticatedUserId)
                ->whereKey($ideaId)
                ->firstOrFail();

            return $this->mapIdea($idea, $authenticatedUserId, $this->resolveIsDeveloper($authenticatedUserId));
        } catch (Throwable $throwable) {
            Log::error('Failed to update idea workflow in repository.', [
                'idea_id' => $ideaId,
                'taken_by_user_id' => $takenByUserId,
                'status' => $status,
                'authenticated_user_id' => $authenticatedUserId,
                'exception' => class_basename($throwable),
                'error' => $throwable->getMessage(),
            ]);

            throw (new IdeaRepositoryError('Unable to update idea workflow.'))->causeBy($throwable);
        }
    }

    public function findIdeasByAssigneeAndStatus(int $assigneeUserId, string $status, int $authenticatedUserId): array
    {
        try {
            $isDeveloper = $this->resolveIsDeveloper($authenticatedUserId);

            return $this->baseIdeasQuery($authenticatedUserId)
                ->where('taken_by_user_id', $assigneeUserId)
                ->where('status', $status)
                ->orderByDesc('updated_at')
                ->get()
                ->map(fn (Idea $idea): array => $this->mapIdea($idea, $authenticatedUserId, $isDeveloper))
                ->all();
        } catch (Throwable $throwable) {
            Log::error('Failed to load ideas by assignee and status from repository.', [
                'assignee_user_id' => $assigneeUserId,
                'status' => $status,
                'authenticated_user_id' => $authenticatedUserId,
                'exception' => class_basename($throwable),
                'error' => $throwable->getMessage(),
            ]);

            throw (new IdeaRepositoryError('Unable to load developer portfolio ideas.'))->causeBy($throwable);
        }
    }

    public function findIdeaUpdates(int $ideaId): array
    {
        try {
            return IdeaUpdate::query()
                ->with('user')
                ->where('idea_id', $ideaId)
                ->orderByDesc('created_at')
                ->get()
                ->map(fn (IdeaUpdate $ideaUpdate): array => $this->mapIdeaUpdate($ideaUpdate))
                ->all();
        } catch (Throwable $throwable) {
            Log::error('Failed to load idea updates from repository.', [
                'idea_id' => $ideaId,
                'exception' => class_basename($throwable),
                'error' => $throwable->getMessage(),
            ]);

            throw (new IdeaRepositoryError('Unable to load idea updates.'))->causeBy($throwable);
        }
    }

    public function createIdeaUpdate(int $ideaId, int $userId, string $text): array
    {
        try {
            $ideaUpdate = IdeaUpdate::query()->create([
                'idea_id' => $ideaId,
                'user_id' => $userId,
                'text' => $text,
            ]);

            $ideaUpdate->load('user');

            return $this->mapIdeaUpdate($ideaUpdate);
        } catch (Throwable $throwable) {
            Log::error('Failed to create idea update in repository.', [
                'idea_id' => $ideaId,
                'user_id' => $userId,
                'exception' => class_basename($throwable),
                'error' => $throwable->getMessage(),
            ]);

            throw (new IdeaRepositoryError('Unable to create idea update.'))->causeBy($throwable);
        }
    }

    private function baseIdeasQuery(int $authenticatedUserId)
    {
        return Idea::query()
            ->with(['category', 'user', 'takenByUser'])
            ->withCount([
                'interactions as upvotes_count' => fn ($query) => $query->where('state', 'upvote'),
                'interactions as downvotes_count' => fn ($query) => $query->where('state', 'downvote'),
            ])
            ->addSelect([
                'user_vote' => IdeaInteraction::query()
                    ->select('state')
                    ->whereColumn('idea_id', 'ideas.id')
                    ->where('user_id', $authenticatedUserId)
                    ->limit(1),
            ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function mapIdea(Idea $idea, int $authenticatedUserId, bool $isDeveloper): array
    {
        $status = (string) $idea->status;
        $isTaken = $idea->taken_by_user_id !== null;
        $isAssignedDeveloper = (int) ($idea->taken_by_user_id ?? 0) === $authenticatedUserId;

        return [
            'id' => (int) $idea->id,
            'user_id' => (int) $idea->user_id,
            'taken_by_user_id' => isset($idea->taken_by_user_id) ? (int) $idea->taken_by_user_id : null,
            'category_id' => (int) $idea->category_id,
            'user' => $idea->user ? $this->mapUser($idea->user) : null,
            'taken_by_user' => $idea->takenByUser ? $this->mapUser($idea->takenByUser) : null,
            'category' => $idea->category ? [
                'id' => (int) $idea->category->id,
                'name' => $idea->category->name,
            ] : null,
            'title' => $idea->title,
            'description' => $idea->description,
            'status' => $status,
            'is_taken' => $isTaken,
            'can_take' => $isDeveloper
                && ! $isTaken
                && $status === 'open'
                && (int) $idea->user_id !== $authenticatedUserId,
            'can_leave' => $isDeveloper
                && $isAssignedDeveloper
                && $status === 'in_progress',
            'can_complete' => $isDeveloper
                && $isAssignedDeveloper
                && $status === 'in_progress',
            'upvotes_count' => (int) ($idea->upvotes_count ?? 0),
            'downvotes_count' => (int) ($idea->downvotes_count ?? 0),
            'user_vote' => (string) ($idea->user_vote ?? 'neutral'),
            'created_at' => $idea->created_at?->toISOString(),
            'updated_at' => $idea->updated_at?->toISOString(),
        ];
    }

    private function resolveIsDeveloper(int $userId): bool
    {
        $user = User::query()->find($userId);

        if (! $user) {
            return false;
        }

        return $user->hasRole('Developer');
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

    /**
     * @return array<string, mixed>
     */
    private function mapIdeaUpdate(IdeaUpdate $ideaUpdate): array
    {
        return [
            'id' => (int) $ideaUpdate->id,
            'idea_id' => (int) $ideaUpdate->idea_id,
            'user_id' => (int) $ideaUpdate->user_id,
            'text' => (string) $ideaUpdate->text,
            'user' => $ideaUpdate->user ? $this->mapUser($ideaUpdate->user) : null,
            'created_at' => $ideaUpdate->created_at?->toISOString(),
            'updated_at' => $ideaUpdate->updated_at?->toISOString(),
        ];
    }
}
