<?php

namespace App\Modules\Ideas\Services;

use App\Exceptions\IdeaRepositoryError;
use App\Exceptions\IdeasDomainError;
use App\Exceptions\UserRepositoryError;
use App\Modules\Ideas\Repositories\IdeaRepositoryInterface;
use App\Modules\Users\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\Log;
use Throwable;

class IdeasService
{
    public function __construct(
        private readonly IdeaRepositoryInterface $ideaRepository,
        private readonly UserRepositoryInterface $userRepository
    ) {
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
        } catch (IdeaRepositoryError|UserRepositoryError $throwable) {
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
        } catch (IdeaRepositoryError|UserRepositoryError $throwable) {
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
     * @return array<string, mixed>
     */
    public function getIdeaById(int $ideaId, int $authenticatedUserId): array
    {
        try {
            $idea = $this->ideaRepository->findIdeaById($ideaId, $authenticatedUserId);

            if (! $idea) {
                throw new IdeasDomainError('The requested idea was not found.', status: 404);
            }

            return [
                'idea' => $idea,
            ];
        } catch (IdeasDomainError $throwable) {
            throw $throwable;
        } catch (IdeaRepositoryError $throwable) {
            Log::error('Unable to load idea details due to repository error.', [
                'idea_id' => $ideaId,
                'user_id' => $authenticatedUserId,
                'exception' => class_basename($throwable),
                'error' => $throwable->getMessage(),
            ]);

            throw (new IdeasDomainError('Unable to load the requested idea.'))->causeBy($throwable);
        } catch (Throwable $throwable) {
            Log::error('Unable to load idea details due to unexpected error.', [
                'idea_id' => $ideaId,
                'user_id' => $authenticatedUserId,
                'exception' => class_basename($throwable),
                'error' => $throwable->getMessage(),
            ]);

            throw (new IdeasDomainError('Unable to load the requested idea.'))->causeBy($throwable);
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

    /**
     * @return array<string, mixed>
     */
    public function takeIdea(int $ideaId, int $authenticatedUserId): array
    {
        try {
            $authenticatedUser = $this->requireAuthenticatedUser($authenticatedUserId);

            if (! $this->isDeveloper($authenticatedUser)) {
                throw new IdeasDomainError('Only developers can take ideas.', status: 403);
            }

            $idea = $this->ideaRepository->findIdeaById($ideaId, $authenticatedUserId);

            if (! $idea) {
                throw new IdeasDomainError('The requested idea was not found.', status: 404);
            }

            if ((int) $idea['user_id'] === $authenticatedUserId) {
                throw new IdeasDomainError('You cannot take your own idea.');
            }

            if ((string) ($idea['status'] ?? '') !== 'open') {
                throw new IdeasDomainError('Only open ideas can be taken.');
            }

            if (isset($idea['taken_by_user_id']) && $idea['taken_by_user_id'] !== null) {
                throw new IdeasDomainError('This idea has already been taken.');
            }

            return [
                'idea' => $this->ideaRepository->updateIdeaWorkflow($ideaId, $authenticatedUserId, 'in_progress', $authenticatedUserId),
            ];
        } catch (IdeasDomainError $throwable) {
            throw $throwable;
        } catch (IdeaRepositoryError $throwable) {
            Log::error('Unable to take idea due to repository error.', [
                'idea_id' => $ideaId,
                'user_id' => $authenticatedUserId,
                'exception' => class_basename($throwable),
                'error' => $throwable->getMessage(),
            ]);

            throw (new IdeasDomainError('Unable to take the idea.'))->causeBy($throwable);
        } catch (Throwable $throwable) {
            Log::error('Unable to take idea due to unexpected error.', [
                'idea_id' => $ideaId,
                'user_id' => $authenticatedUserId,
                'exception' => class_basename($throwable),
                'error' => $throwable->getMessage(),
            ]);

            throw (new IdeasDomainError('Unable to take the idea.'))->causeBy($throwable);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function leaveIdea(int $ideaId, int $authenticatedUserId): array
    {
        try {
            $idea = $this->ideaRepository->findIdeaById($ideaId, $authenticatedUserId);

            if (! $idea) {
                throw new IdeasDomainError('The requested idea was not found.', status: 404);
            }

            if ((int) ($idea['taken_by_user_id'] ?? 0) !== $authenticatedUserId) {
                throw new IdeasDomainError('Only the assigned developer can leave this idea.', status: 403);
            }

            if ((string) ($idea['status'] ?? '') !== 'in_progress') {
                throw new IdeasDomainError('Only in-progress ideas can be left.');
            }

            return [
                'idea' => $this->ideaRepository->updateIdeaWorkflow($ideaId, null, 'open', $authenticatedUserId),
            ];
        } catch (IdeasDomainError $throwable) {
            throw $throwable;
        } catch (IdeaRepositoryError $throwable) {
            Log::error('Unable to leave idea due to repository error.', [
                'idea_id' => $ideaId,
                'user_id' => $authenticatedUserId,
                'exception' => class_basename($throwable),
                'error' => $throwable->getMessage(),
            ]);

            throw (new IdeasDomainError('Unable to leave the idea.'))->causeBy($throwable);
        } catch (Throwable $throwable) {
            Log::error('Unable to leave idea due to unexpected error.', [
                'idea_id' => $ideaId,
                'user_id' => $authenticatedUserId,
                'exception' => class_basename($throwable),
                'error' => $throwable->getMessage(),
            ]);

            throw (new IdeasDomainError('Unable to leave the idea.'))->causeBy($throwable);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function completeIdea(int $ideaId, int $authenticatedUserId): array
    {
        try {
            $idea = $this->ideaRepository->findIdeaById($ideaId, $authenticatedUserId);

            if (! $idea) {
                throw new IdeasDomainError('The requested idea was not found.', status: 404);
            }

            if ((int) ($idea['taken_by_user_id'] ?? 0) !== $authenticatedUserId) {
                throw new IdeasDomainError('Only the assigned developer can complete this idea.', status: 403);
            }

            if ((string) ($idea['status'] ?? '') !== 'in_progress') {
                throw new IdeasDomainError('Only in-progress ideas can be completed.');
            }

            return [
                'idea' => $this->ideaRepository->updateIdeaWorkflow($ideaId, $authenticatedUserId, 'completed', $authenticatedUserId),
            ];
        } catch (IdeasDomainError $throwable) {
            throw $throwable;
        } catch (IdeaRepositoryError $throwable) {
            Log::error('Unable to complete idea due to repository error.', [
                'idea_id' => $ideaId,
                'user_id' => $authenticatedUserId,
                'exception' => class_basename($throwable),
                'error' => $throwable->getMessage(),
            ]);

            throw (new IdeasDomainError('Unable to complete the idea.'))->causeBy($throwable);
        } catch (Throwable $throwable) {
            Log::error('Unable to complete idea due to unexpected error.', [
                'idea_id' => $ideaId,
                'user_id' => $authenticatedUserId,
                'exception' => class_basename($throwable),
                'error' => $throwable->getMessage(),
            ]);

            throw (new IdeasDomainError('Unable to complete the idea.'))->causeBy($throwable);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function getDeveloperPortfolio(int $authenticatedUserId): array
    {
        try {
            $user = $this->requireAuthenticatedUser($authenticatedUserId);

            if (! $this->isDeveloper($user)) {
                return [
                    'currently_working_on' => [],
                    'completed_ideas' => [],
                ];
            }

            return [
                'currently_working_on' => $this->ideaRepository->findIdeasByAssigneeAndStatus($authenticatedUserId, 'in_progress', $authenticatedUserId),
                'completed_ideas' => $this->ideaRepository->findIdeasByAssigneeAndStatus($authenticatedUserId, 'completed', $authenticatedUserId),
            ];
        } catch (IdeasDomainError $throwable) {
            throw $throwable;
        } catch (IdeaRepositoryError $throwable) {
            Log::error('Unable to load developer portfolio due to repository error.', [
                'user_id' => $authenticatedUserId,
                'exception' => class_basename($throwable),
                'error' => $throwable->getMessage(),
            ]);

            throw (new IdeasDomainError('Unable to load developer portfolio.'))->causeBy($throwable);
        } catch (Throwable $throwable) {
            Log::error('Unable to load developer portfolio due to unexpected error.', [
                'user_id' => $authenticatedUserId,
                'exception' => class_basename($throwable),
                'error' => $throwable->getMessage(),
            ]);

            throw (new IdeasDomainError('Unable to load developer portfolio.'))->causeBy($throwable);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function listIdeaUpdates(int $ideaId, int $authenticatedUserId): array
    {
        try {
            $idea = $this->ideaRepository->findIdeaById($ideaId, $authenticatedUserId);

            if (! $idea) {
                throw new IdeasDomainError('The requested idea was not found.', status: 404);
            }

            return [
                'updates' => $this->ideaRepository->findIdeaUpdates($ideaId),
            ];
        } catch (IdeasDomainError $throwable) {
            throw $throwable;
        } catch (IdeaRepositoryError $throwable) {
            Log::error('Unable to list idea updates due to repository error.', [
                'idea_id' => $ideaId,
                'user_id' => $authenticatedUserId,
                'exception' => class_basename($throwable),
                'error' => $throwable->getMessage(),
            ]);

            throw (new IdeasDomainError('Unable to load idea updates.'))->causeBy($throwable);
        } catch (Throwable $throwable) {
            Log::error('Unable to list idea updates due to unexpected error.', [
                'idea_id' => $ideaId,
                'user_id' => $authenticatedUserId,
                'exception' => class_basename($throwable),
                'error' => $throwable->getMessage(),
            ]);

            throw (new IdeasDomainError('Unable to load idea updates.'))->causeBy($throwable);
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function createIdeaUpdate(int $ideaId, int $authenticatedUserId, array $payload): array
    {
        try {
            $idea = $this->ideaRepository->findIdeaById($ideaId, $authenticatedUserId);

            if (! $idea) {
                throw new IdeasDomainError('The requested idea was not found.', status: 404);
            }

            if ((int) ($idea['taken_by_user_id'] ?? 0) !== $authenticatedUserId) {
                throw new IdeasDomainError('Only the assigned developer can post updates.', status: 403);
            }

            if ((string) ($idea['status'] ?? '') !== 'in_progress') {
                throw new IdeasDomainError('Updates can only be posted while an idea is in progress.');
            }

            return [
                'update' => $this->ideaRepository->createIdeaUpdate(
                    $ideaId,
                    $authenticatedUserId,
                    (string) $payload['text']
                ),
            ];
        } catch (IdeasDomainError $throwable) {
            throw $throwable;
        } catch (IdeaRepositoryError $throwable) {
            Log::error('Unable to create idea update due to repository error.', [
                'idea_id' => $ideaId,
                'user_id' => $authenticatedUserId,
                'exception' => class_basename($throwable),
                'error' => $throwable->getMessage(),
            ]);

            throw (new IdeasDomainError('Unable to create idea update.'))->causeBy($throwable);
        } catch (Throwable $throwable) {
            Log::error('Unable to create idea update due to unexpected error.', [
                'idea_id' => $ideaId,
                'user_id' => $authenticatedUserId,
                'exception' => class_basename($throwable),
                'error' => $throwable->getMessage(),
            ]);

            throw (new IdeasDomainError('Unable to create idea update.'))->causeBy($throwable);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function requireAuthenticatedUser(int $authenticatedUserId): array
    {
        $user = $this->userRepository->findUserById($authenticatedUserId);

        if (! $user) {
            throw new IdeasDomainError('Authenticated user was not found.', status: 404);
        }

        return $user;
    }

    /**
     * @param  array<string, mixed>  $user
     */
    private function isDeveloper(array $user): bool
    {
        $roles = array_map(
            static fn (mixed $role): string => strtolower((string) $role),
            is_array($user['roles'] ?? null) ? $user['roles'] : []
        );

        return in_array('developer', $roles, true);
    }
}
