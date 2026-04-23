<?php

namespace App\Modules\Categories\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Categories\Services\CategoriesService;
use App\Utils\ResponseHelper;
use Illuminate\Http\JsonResponse;

class CategoriesController extends Controller
{
    public function __construct(private readonly CategoriesService $categoriesService)
    {
    }

    public function index(): JsonResponse
    {
        $response = $this->categoriesService->listCategories();

        return response()->json(ResponseHelper::success($response));
    }
}
