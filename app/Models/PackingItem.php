<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackingItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'trip_id', 'name', 'category', 'is_packed', 'quantity', 'notes', 'sort_order',
    ];

    protected $casts = [
        'is_packed' => 'boolean',
    ];

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function getCategoryIconAttribute(): string
    {
        return match($this->category) {
            'clothing' => '👕',
            'essentials' => '🎒',
            'electronics' => '💻',
            'toiletries' => '🪥',
            'documents' => '📄',
            'health' => '💊',
            'entertainment' => '🎮',
            default => '📦',
        };
    }
}
