<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IdeaComment extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'idea_id',
        'user_id',
        'text',
        'root_comment_id',
        'parent_id',
    ];

    public function idea(): BelongsTo
    {
        return $this->belongsTo(Idea::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function rootComment(): BelongsTo
    {
        return $this->belongsTo(self::class, 'root_comment_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'root_comment_id')
            ->orderBy('created_at');
    }

    public function interactions(): HasMany
    {
        return $this->hasMany(CommentInteraction::class, 'comment_id');
    }

    public function interactingUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'comment_interactions', 'comment_id', 'user_id')
            ->withPivot('state')
            ->withTimestamps();
    }
}
