<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TravelPost extends Model
{
    protected $fillable = [
        'user_id', 'trip_id', 'title', 'body', 'photos',
        'visibility', 'likes_count', 'comments_count', 'views_count',
    ];

    protected $casts = [
        'photos'         => 'array',
        'likes_count'    => 'integer',
        'comments_count' => 'integer',
        'views_count'    => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class)->withDefault();
    }

    public function comments(): HasMany
    {
        return $this->hasMany(PostComment::class)->latest();
    }

    public function likes(): HasMany
    {
        return $this->hasMany(PostLike::class);
    }

    public function isLikedBy(?User $user): bool
    {
        if (!$user) return false;
        return $this->likes()->where('user_id', $user->id)->exists();
    }

    public function getExcerptAttribute(): string
    {
        return \Str::limit(strip_tags($this->body), 160);
    }

    public function getCoverPhotoAttribute(): ?string
    {
        return $this->photos[0] ?? null;
    }
}
