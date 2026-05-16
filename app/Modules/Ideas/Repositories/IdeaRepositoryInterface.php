<?php

namespace App\Modules\Ideas\Repositories;

interface IdeaRepositoryInterface
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function findIdeasByOwnerId(int $ownerUserId, int $authenticatedUserId): array;

    /**
     * @return array<string, mixed>|null
     */
    public function findIdeaById(int $ideaId, int $authenticatedUserId): ?array;

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function findIdeas(array $filters, int $authenticatedUserId): array;

    /**
     * @param  array<string, mixed>  $ideaData
     * @return array<string, mixed>
     */
    public function createIdea(array $ideaData): array;

    public function existsById(int $ideaId): bool;

    /**
     * @return array<string, mixed>
     */
    public function setIdeaInteraction(int $ideaId, int $userId, string $state): array;

    /**
     * @return array<string, mixed>
     */
    public function updateIdeaWorkflow(int $ideaId, ?int $takenByUserId, string $status, int $authenticatedUserId): array;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function findIdeasByAssigneeAndStatus(int $assigneeUserId, string $status, int $authenticatedUserId): array;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function findIdeaUpdates(int $ideaId): array;

    /**
     * @return array<string, mixed>
     */
    public function createIdeaUpdate(int $ideaId, int $userId, string $text): array;
}
