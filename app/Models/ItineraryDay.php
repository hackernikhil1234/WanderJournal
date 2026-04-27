<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ItineraryDay extends Model
{
    use HasFactory;

    protected $fillable = [
        'trip_id', 'day_number', 'date', 'title', 'notes', 'theme_color',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ItineraryItem::class)->orderBy('sort_order');
    }

    public function getDayLabelAttribute(): string
    {
        return 'Day ' . $this->day_number . ($this->date ? ' — ' . $this->date->format('M d, Y') : '');
    }

    public function getDayCostAttribute(): float
    {
        return $this->items->sum('cost') ?? 0;
    }
}
