<?php

namespace App\Modules\Ideas\Repositories;

interface IdeaRepositoryInterface
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function findIdeasByOwnerId(int $ownerUserId): array;

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function findIdeas(array $filters): array;

    /**
     * @param  array<string, mixed>  $ideaData
     * @return array<string, mixed>
     */
    public function createIdea(array $ideaData): array;
}
