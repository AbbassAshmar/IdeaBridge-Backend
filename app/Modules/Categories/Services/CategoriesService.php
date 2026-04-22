<?php

namespace App\Modules\Categories\Services;

use App\Exceptions\CategoriesDomainError;
use App\Exceptions\CategoryRepositoryError;
use App\Modules\Categories\Repositories\CategoryRepositoryInterface;
use Throwable;

class CategoriesService
{
    public function __construct(private readonly CategoryRepositoryInterface $categoryRepository)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function listCategories(): array
    {
        try {
            return [
                'categories' => $this->categoryRepository->findAllCategories(),
            ];
        } catch (CategoryRepositoryError $throwable) {
            throw (new CategoriesDomainError('Unable to load categories.'))->causeBy($throwable);
        } catch (Throwable $throwable) {
            throw $throwable;
        }
    }
}