<?php

namespace App\Modules\Ideas\Services;

use App\Exceptions\IdeaRepositoryError;
use App\Exceptions\IdeasDomainError;
use App\Modules\Ideas\Repositories\IdeaRepositoryInterface;
use Throwable;

class IdeasService
{
    public function __construct(private readonly IdeaRepositoryInterface $ideaRepository)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function listIdeasForUser(int $authenticatedUserId): array
    {
        try {
            return [
                'ideas' => $this->ideaRepository->findIdeasByOwnerId($authenticatedUserId),
            ];
        } catch (IdeaRepositoryError $throwable) {
            throw (new IdeasDomainError('Unable to load your ideas.'))->causeBy($throwable);
        }
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function listIdeas(array $filters): array
    {
        try {
            return $this->ideaRepository->findIdeas($filters);
        } catch (IdeaRepositoryError $throwable) {
            throw (new IdeasDomainError('Unable to load ideas.'))->causeBy($throwable);
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function createIdea(int $creatorUserId, array $payload): array
    {
        try {
            return [
                'idea' => $this->ideaRepository->createIdea([
                    'user_id' => $creatorUserId,
                    'category_id' => (int) $payload['category_id'],
                    'title' => (string) $payload['title'],
                    'description' => (string) $payload['description'],
                ]),
            ];
        } catch (IdeaRepositoryError $throwable) {
            throw (new IdeasDomainError('Unable to create the idea.'))->causeBy($throwable);
        }
    }
}
