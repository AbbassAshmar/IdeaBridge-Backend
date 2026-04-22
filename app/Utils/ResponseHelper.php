<?php

namespace App\Utils;

final class ResponseHelper
{
    /**
     * @param  array<string, mixed>  $meta
     * @return array<string, mixed>
     */
    public static function success(mixed $data = null, array $meta = []): array
    {
        return [
            'data' => $data,
            'error' => null,
            'meta' => (object) $meta,
        ];
    }

    /**
     * @param  array<string, mixed>  $error
     * @param  array<string, mixed>  $meta
     * @return array<string, mixed>
     */
    public static function failure(array $error, mixed $data = null, array $meta = []): array
    {
        return [
            'data' => $data,
            'error' => $error,
            'meta' => (object) $meta,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function paginationMeta(int $totalCount, int $page, int $limit): array
    {
        $limit = max(1, $limit);

        return [
            'pagination' => [
                'total_count' => $totalCount,
                'page' => $page,
                'limit' => $limit,
                'total_pages' => (int) ceil($totalCount / $limit),
            ],
        ];
    }
}