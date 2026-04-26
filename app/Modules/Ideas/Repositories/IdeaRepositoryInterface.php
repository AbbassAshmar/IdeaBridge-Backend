<?php

namespace App\Modules\Ideas\Repositories;

interface IdeaRepositoryInterface
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function findIdeasByOwnerId(int $ownerUserId, int $authenticatedUserId): array;

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
}
