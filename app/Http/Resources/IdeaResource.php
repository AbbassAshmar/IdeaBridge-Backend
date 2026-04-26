<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IdeaResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $idea = is_array($this->resource) ? $this->resource : $this->resource->toArray();

        return [
            'id' => (int) ($idea['id'] ?? 0),
            'user_id' => (int) ($idea['user_id'] ?? 0),
            'taken_by_user_id' => isset($idea['taken_by_user_id']) ? (int) $idea['taken_by_user_id'] : null,
            'category_id' => (int) ($idea['category_id'] ?? 0),
            'category' => $idea['category'] ?? null,
            'title' => (string) ($idea['title'] ?? ''),
            'description' => (string) ($idea['description'] ?? ''),
            'status' => (string) ($idea['status'] ?? ''),
            'upvotes_count' => (int) ($idea['upvotes_count'] ?? 0),
            'downvotes_count' => (int) ($idea['downvotes_count'] ?? 0),
            'user_vote' => (string) ($idea['user_vote'] ?? 'neutral'),
            'created_at' => $idea['created_at'] ?? null,
            'updated_at' => $idea['updated_at'] ?? null,
        ];
    }
}
