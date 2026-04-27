<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'trip_id', 'type', 'title', 'provider', 'booking_ref',
        'price', 'currency', 'check_in', 'check_out', 'from_location',
        'to_location', 'details', 'image', 'status',
    ];

    protected $casts = [
        'check_in' => 'date',
        'check_out' => 'date',
        'price' => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function getTypeIconAttribute(): string
    {
        return match($this->type) {
            'flight' => '✈️',
            'hotel' => '🏨',
            'activity' => '🎯',
            'car_rental' => '🚗',
            'cruise' => '🚢',
            'tour' => '🗺️',
            default => '📋',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'confirmed' => 'green',
            'pending' => 'yellow',
            'cancelled' => 'red',
            'completed' => 'blue',
            default => 'gray',
        };
    }
}
