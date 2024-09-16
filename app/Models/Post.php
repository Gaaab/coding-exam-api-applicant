<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [
        'id',
        'user_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

/**
     * Scope a query to search posts.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $searchTerm
     * @param \App\Models\User $user
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearchPosts($query, $searchTerm, User $user)
    {
        if ($user->is_admin) {
            // Admin can search through all posts
            return $query->where('title', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('body', 'LIKE', "%{$searchTerm}%");
        } else {
            // Regular users can only search their own posts
            return $query->where('user_id', $user->id)
                        ->where(function ($query) use ($searchTerm) {
                            $query->where('title', 'LIKE', "%{$searchTerm}%")
                                ->orWhere('body', 'LIKE', "%{$searchTerm}%");
                        });
        }
    }
}
