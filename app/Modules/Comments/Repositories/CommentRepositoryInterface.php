<?php

namespace App\Modules\Comments\Repositories;

interface CommentRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function findIdeaComments(int $ideaId, array $filters, int $authenticatedUserId): array;

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function findUserComments(int $userId, array $filters): array;

    /**
     * @return array<string, mixed>|null
     */
    public function findCommentContextById(int $commentId): ?array;

    /**
     * @return array<string, mixed>
     */
    public function createComment(
        int $ideaId,
        int $userId,
        string $text,
        ?int $parentId,
        ?int $rootCommentId
    ): array;

    public function existsById(int $commentId): bool;

    /**
     * @return array<string, mixed>
     */
    public function setCommentInteraction(int $commentId, int $userId, string $state): array;
}
