<?php

namespace App\Modules\Comments\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserCommentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                    => (int) $this['id'],
            'idea_id'               => (int) $this['idea_id'],
            'text'                  => (string) $this['text'],
            'created_at'            => $this['created_at'],
            'user'                  => $this['user'],
            'upvotes_count'         => (int) ($this['upvotes_count']   ?? 0),
            'downvotes_count'       => (int) ($this['downvotes_count'] ?? 0),
            'auth_user_interaction' => $this['auth_user_interaction'] ?? null,
            'parent'                => $this['parent'] ?? null,
        ];
    }
}