<?php

namespace App\Modules\Categories\Services;

use App\Exceptions\CategoriesDomainError;
use App\Exceptions\CategoryRepositoryError;
use App\Modules\Categories\Repositories\CategoryRepositoryInterface;
use Illuminate\Support\Facades\Log;
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
            $response = [
                'categories' => $this->categoryRepository->findAllCategories(),
            ];

            Log::info('Categories loaded successfully.', [
                'count' => count($response['categories']),
            ]);

            return $response;
        } catch (CategoryRepositoryError $throwable) {
            Log::error('Unable to load categories due to repository error.', [
                'exception' => class_basename($throwable),
                'error' => $throwable->getMessage(),
            ]);

            throw (new CategoriesDomainError('Unable to load categories.'))->causeBy($throwable);
        } catch (Throwable $throwable) {
            Log::error('Unable to load categories due to unexpected error.', [
                'exception' => class_basename($throwable),
                'error' => $throwable->getMessage(),
            ]);

            throw (new CategoriesDomainError('Unable to load categories.'))->causeBy($throwable);
        }
    }
}