<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IdeaCategory extends Model
{
    use HasFactory;

    protected $table = 'ideas_categories';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
    ];

    public function ideas(): HasMany
    {
        return $this->hasMany(Idea::class, 'category_id');
    }
}