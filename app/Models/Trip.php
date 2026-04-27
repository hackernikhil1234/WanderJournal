<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Trip extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'destination_id', 'title', 'start_date', 'end_date',
        'num_days', 'budget', 'currency', 'travel_style', 'interests',
        'num_travelers', 'status', 'notes', 'estimated_cost', 'is_public',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'budget' => 'float',
        'estimated_cost' => 'float',
        'is_public' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }

    public function itineraryDays(): HasMany
    {
        return $this->hasMany(ItineraryDay::class)->orderBy('day_number');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function packingItems(): HasMany
    {
        return $this->hasMany(PackingItem::class);
    }

    public function getTotalCostAttribute(): float
    {
        return $this->itineraryDays->flatMap->items->sum('cost') ?? 0;
    }

    public function getPackingProgressAttribute(): int
    {
        $total = $this->packingItems()->count();
        if ($total === 0) return 0;
        $packed = $this->packingItems()->where('is_packed', true)->count();
        return (int) round(($packed / $total) * 100);
    }
}
