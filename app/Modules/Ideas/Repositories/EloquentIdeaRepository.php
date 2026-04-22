<?php

namespace App\Modules\Ideas\Repositories;

use App\Exceptions\IdeaRepositoryError;
use App\Models\Idea;
use Throwable;

class EloquentIdeaRepository implements IdeaRepositoryInterface
{
    public function findIdeasByOwnerId(int $ownerUserId): array
    {
        try {
            return Idea::query()
                ->with('category')
                ->where('user_id', $ownerUserId)
                ->orderByDesc('created_at')
                ->get()
                ->map(fn (Idea $idea): array => $this->mapIdea($idea))
                ->all();
        } catch (Throwable $throwable) {
            throw (new IdeaRepositoryError('Unable to load ideas for the requested user.'))->causeBy($throwable);
        }
    }

    public function findIdeas(array $filters): array
    {
        try {
            $page = max(1, (int) ($filters['page'] ?? 1));
            $limit = max(1, min(100, (int) ($filters['limit'] ?? 15)));
            $search = trim((string) ($filters['q'] ?? ''));
            $sort = strtolower((string) ($filters['sort'] ?? 'desc'));

            $query = Idea::query()->with('category');

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

            $idea->load('category');

            return $this->mapIdea($idea);
        } catch (Throwable $throwable) {
            throw (new IdeaRepositoryError('Unable to create the idea.'))->causeBy($throwable);
        }
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
            'category' => $idea->category ? [
                'id' => $idea->category->id,
                'name' => $idea->category->name,
            ] : null,
            'title' => $idea->title,
            'description' => $idea->description,
            'status' => $idea->status,
            'created_at' => $idea->created_at?->toISOString(),
            'updated_at' => $idea->updated_at?->toISOString(),
        ];
    }
}
