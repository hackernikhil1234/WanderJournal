<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItineraryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'itinerary_day_id', 'title', 'description', 'location',
        'latitude', 'longitude', 'start_time', 'end_time',
        'type', 'cost', 'image', 'booking_ref', 'notes', 'sort_order',
    ];

    protected $casts = [
        'cost' => 'float',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public function day(): BelongsTo
    {
        return $this->belongsTo(ItineraryDay::class, 'itinerary_day_id');
    }

    public function getTypeIconAttribute(): string
    {
        return match($this->type) {
            'attraction' => '🏛️',
            'restaurant' => '🍽️',
            'hotel' => '🏨',
            'transport' => '🚌',
            'activity' => '🎯',
            'shopping' => '🛍️',
            default => '📍',
        };
    }

    public function getTypeBadgeColorAttribute(): string
    {
        return match($this->type) {
            'attraction' => 'amber',
            'restaurant' => 'red',
            'hotel' => 'blue',
            'transport' => 'gray',
            'activity' => 'green',
            'shopping' => 'purple',
            default => 'stone',
        };
    }
}
