<?php

namespace App\Modules\Ideas\Repositories;

use App\Exceptions\IdeaRepositoryError;
use App\Models\Idea;
use App\Models\IdeaInteraction;
use Illuminate\Support\Facades\Log;
use Throwable;

class EloquentIdeaRepository implements IdeaRepositoryInterface
{
    public function findIdeasByOwnerId(int $ownerUserId, int $authenticatedUserId): array
    {
        try {
            return $this->baseIdeasQuery($authenticatedUserId)
                ->where('user_id', $ownerUserId)
                ->orderByDesc('created_at')
                ->get()
                ->map(fn (Idea $idea): array => $this->mapIdea($idea))
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

    public function findIdeas(array $filters, int $authenticatedUserId): array
    {
        try {
            $page = max(1, (int) ($filters['page'] ?? 1));
            $limit = max(1, min(100, (int) ($filters['limit'] ?? 15)));
            $search = trim((string) ($filters['q'] ?? ''));
            $sort = strtolower((string) ($filters['sort'] ?? 'desc'));

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
                    'ideas' => $paginator->getCollection()->map(fn (Idea $idea): array => $this->mapIdea($idea))->values()->all(),
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
                'status' => 'available',
            ]);

            $idea = $this->baseIdeasQuery((int) $ideaData['user_id'])
                ->whereKey($idea->id)
                ->firstOrFail();

            return $this->mapIdea($idea);
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
    private function mapIdea(Idea $idea): array
    {
        return [
            'id' => $idea->id,
            'user_id' => $idea->user_id,
            'taken_by_user_id' => $idea->taken_by_user_id,
            'category_id' => $idea->category_id,
            'user' => $idea->user ? [
                'id' => $idea->user->id,
                'username' => $idea->user->username,
                'email' => $idea->user->email,
            ] : null,
            'taken_by_user' => $idea->takenByUser ? [
                'id' => $idea->takenByUser->id,
                'username' => $idea->takenByUser->username,
                'email' => $idea->takenByUser->email,
            ] : null,
            'category' => $idea->category ? [
                'id' => $idea->category->id,
                'name' => $idea->category->name,
            ] : null,
            'title' => $idea->title,
            'description' => $idea->description,
            'status' => $idea->status,
            'upvotes_count' => (int) ($idea->upvotes_count ?? 0),
            'downvotes_count' => (int) ($idea->downvotes_count ?? 0),
            'user_vote' => (string) ($idea->user_vote ?? 'neutral'),
            'created_at' => $idea->created_at?->toISOString(),
            'updated_at' => $idea->updated_at?->toISOString(),
        ];
    }
}
