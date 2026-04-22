<?php

namespace App\Modules\Categories\Repositories;

use App\Exceptions\CategoryRepositoryError;
use App\Models\IdeaCategory;
use Throwable;

class EloquentCategoryRepository implements CategoryRepositoryInterface
{
    public function findAllCategories(): array
    {
        try {
            return IdeaCategory::query()
                ->orderBy('name')
                ->get()
                ->map(fn (IdeaCategory $category): array => $this->mapCategory($category))
                ->all();
        } catch (Throwable $throwable) {
            throw (new CategoryRepositoryError('Unable to load categories.'))->causeBy($throwable);
        }
    }

    public function findCategoryById(int $categoryId): ?array
    {
        try {
            $category = IdeaCategory::query()->find($categoryId);

            return $category ? $this->mapCategory($category) : null;
        } catch (Throwable $throwable) {
            throw (new CategoryRepositoryError('Unable to load the requested category.'))->causeBy($throwable);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function mapCategory(IdeaCategory $category): array
    {
        return [
            'id' => $category->id,
            'name' => $category->name,
            'created_at' => $category->created_at?->toISOString(),
            'updated_at' => $category->updated_at?->toISOString(),
        ];
    }
}