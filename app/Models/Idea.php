<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Idea extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'taken_by_user_id',
        'category_id',
        'title',
        'description',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function takenByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'taken_by_user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(IdeaCategory::class, 'category_id');
    }

    public function interactions(): HasMany
    {
        return $this->hasMany(IdeaInteraction::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(IdeaComment::class)
            ->whereNull('root_comment_id')
            ->orderByDesc('created_at');
    }

    public function updates(): HasMany
    {
        return $this->hasMany(IdeaUpdate::class)->latest();
    }

    public function interactingUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'idea_interactions')
            ->withPivot('state')
            ->withTimestamps();
    }
}
