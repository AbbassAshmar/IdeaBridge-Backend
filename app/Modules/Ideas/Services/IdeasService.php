<?php

namespace App\Modules\Ideas\Services;

use App\Exceptions\IdeaRepositoryError;
use App\Exceptions\IdeasDomainError;
use App\Modules\Ideas\Repositories\IdeaRepositoryInterface;
use Illuminate\Support\Facades\Log;
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
            $response = [
                'ideas' => $this->ideaRepository->findIdeasByOwnerId($authenticatedUserId, $authenticatedUserId),
            ];

            Log::info('Loaded ideas for authenticated user.', [
                'user_id' => $authenticatedUserId,
                'count' => count($response['ideas']),
            ]);

            return $response;
        } catch (IdeaRepositoryError $throwable) {
            Log::error('Unable to load authenticated user ideas due to repository error.', [
                'user_id' => $authenticatedUserId,
                'exception' => class_basename($throwable),
                'error' => $throwable->getMessage(),
            ]);

            throw (new IdeasDomainError('Unable to load your ideas.'))->causeBy($throwable);
        } catch (Throwable $throwable) {
            Log::error('Unable to load authenticated user ideas due to unexpected error.', [
                'user_id' => $authenticatedUserId,
                'exception' => class_basename($throwable),
                'error' => $throwable->getMessage(),
            ]);

            throw (new IdeasDomainError('Unable to load your ideas.'))->causeBy($throwable);
        }
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function listIdeas(array $filters, int $authenticatedUserId): array
    {
        try {
            $response = $this->ideaRepository->findIdeas($filters, $authenticatedUserId);

            Log::info('Ideas list loaded with filters.', [
                'user_id' => $authenticatedUserId,
                'page' => (int) ($filters['page'] ?? 1),
                'limit' => (int) ($filters['limit'] ?? 15),
                'has_search' => trim((string) ($filters['q'] ?? '')) !== '',
            ]);

            return $response;
        } catch (IdeaRepositoryError $throwable) {
            Log::error('Unable to load ideas due to repository error.', [
                'user_id' => $authenticatedUserId,
                'exception' => class_basename($throwable),
                'error' => $throwable->getMessage(),
            ]);

            throw (new IdeasDomainError('Unable to load ideas.'))->causeBy($throwable);
        } catch (Throwable $throwable) {
            Log::error('Unable to load ideas due to unexpected error.', [
                'user_id' => $authenticatedUserId,
                'exception' => class_basename($throwable),
                'error' => $throwable->getMessage(),
            ]);

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
            $response = [
                'idea' => $this->ideaRepository->createIdea([
                    'user_id' => $creatorUserId,
                    'category_id' => (int) $payload['category_id'],
                    'title' => (string) $payload['title'],
                    'description' => (string) $payload['description'],
                ]),
            ];

            Log::info('Idea created successfully.', [
                'user_id' => $creatorUserId,
                'category_id' => (int) $payload['category_id'],
                'idea_id' => (int) ($response['idea']['id'] ?? 0),
            ]);

            return $response;
        } catch (IdeaRepositoryError $throwable) {
            Log::error('Unable to create idea due to repository error.', [
                'user_id' => $creatorUserId,
                'category_id' => (int) ($payload['category_id'] ?? 0),
                'exception' => class_basename($throwable),
                'error' => $throwable->getMessage(),
            ]);

            throw (new IdeasDomainError('Unable to create the idea.'))->causeBy($throwable);
        } catch (Throwable $throwable) {
            Log::error('Unable to create idea due to unexpected error.', [
                'user_id' => $creatorUserId,
                'category_id' => (int) ($payload['category_id'] ?? 0),
                'exception' => class_basename($throwable),
                'error' => $throwable->getMessage(),
            ]);

            throw (new IdeasDomainError('Unable to create the idea.'))->causeBy($throwable);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function updateInteraction(int $ideaId, int $authenticatedUserId, string $state): array
    {
        try {
            if (! $this->ideaRepository->existsById($ideaId)) {
                throw new IdeasDomainError('The requested idea was not found.', status: 404);
            }

            $response = [
                'interaction' => $this->ideaRepository->setIdeaInteraction($ideaId, $authenticatedUserId, $state),
            ];

            Log::info('Idea interaction updated successfully.', [
                'idea_id' => $ideaId,
                'user_id' => $authenticatedUserId,
                'state' => $state,
            ]);

            return $response;
        } catch (IdeasDomainError $throwable) {
            throw $throwable;
        } catch (IdeaRepositoryError $throwable) {
            Log::error('Unable to update idea interaction due to repository error.', [
                'idea_id' => $ideaId,
                'user_id' => $authenticatedUserId,
                'state' => $state,
                'exception' => class_basename($throwable),
                'error' => $throwable->getMessage(),
            ]);

            throw (new IdeasDomainError('Unable to update idea interaction.'))->causeBy($throwable);
        } catch (Throwable $throwable) {
            Log::error('Unable to update idea interaction due to unexpected error.', [
                'idea_id' => $ideaId,
                'user_id' => $authenticatedUserId,
                'state' => $state,
                'exception' => class_basename($throwable),
                'error' => $throwable->getMessage(),
            ]);

            throw (new IdeasDomainError('Unable to update idea interaction.'))->causeBy($throwable);
        }
    }
}
