<?php

namespace App\Modules\Ideas\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IdeaUpdateResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $update = is_array($this->resource) ? $this->resource : $this->resource->toArray();

        return [
            'id' => (int) ($update['id'] ?? 0),
            'idea_id' => (int) ($update['idea_id'] ?? 0),
            'user_id' => (int) ($update['user_id'] ?? 0),
            'text' => (string) ($update['text'] ?? ''),
            'user' => $update['user'] ?? null,
            'created_at' => $update['created_at'] ?? null,
            'updated_at' => $update['updated_at'] ?? null,
        ];
    }
}
