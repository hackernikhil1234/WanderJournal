<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Destination extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'country', 'continent', 'category',
        'description', 'short_description', 'cover_image', 'gallery_images',
        'best_time_to_visit', 'average_rating', 'reviews_count',
        'avg_daily_budget', 'currency', 'timezone', 'language',
        'latitude', 'longitude', 'highlights', 'tags', 'featured', 'popularity_score',
    ];

    protected $casts = [
        'gallery_images' => 'array',
        'highlights' => 'array',
        'tags' => 'array',
        'featured' => 'boolean',
        'average_rating' => 'float',
        'avg_daily_budget' => 'float',
    ];

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function getCoverImageUrlAttribute(): string
    {
        if ($this->cover_image && str_starts_with($this->cover_image, 'http')) {
            return $this->cover_image;
        }
        return $this->cover_image
            ? asset('storage/' . $this->cover_image)
            : 'https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?w=800&auto=format&fit=crop';
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByCountry($query, $country)
    {
        return $query->where('country', $country);
    }

    public function updateRating(): void
    {
        $avg = $this->reviews()->where('is_approved', true)->avg('rating');
        $count = $this->reviews()->where('is_approved', true)->count();
        $this->update([
            'average_rating' => round($avg ?? 0, 2),
            'reviews_count' => $count,
        ]);
    }
}
