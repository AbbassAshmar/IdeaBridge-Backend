<?php

namespace App\Modules\Categories\Repositories;

interface CategoryRepositoryInterface
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function findAllCategories(): array;

    /**
     * @return array<string, mixed>|null
     */
    public function findCategoryById(int $categoryId): ?array;
}