<?php

namespace App\Modules\Comments\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IdeaCommentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $comment = is_array($this->resource) ? $this->resource : $this->resource->toArray();

        $response = [
            'id' => (int) ($comment['id'] ?? 0),
            'text' => (string) ($comment['text'] ?? ''),
            'created_at' => $comment['created_at'] ?? null,
            'user' => $comment['user'] ?? null,
            'upvotes_count' => (int) ($comment['upvotes_count'] ?? 0),
            'downvotes_count' => (int) ($comment['downvotes_count'] ?? 0),
            'auth_user_interaction' => $comment['auth_user_interaction'] ?? null,
        ];

        if (array_key_exists('parent', $comment)) {
            $response['parent'] = $comment['parent'];
        }

        if (array_key_exists('replies_count', $comment)) {
            $response['replies_count'] = (int) ($comment['replies_count'] ?? 0);
        }

        if (array_key_exists('replies', $comment)) {
            $response['replies'] = self::collection(collect($comment['replies']))->resolve();
        }

        return $response;
    }
}
