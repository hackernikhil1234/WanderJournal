<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'trip_id', 'user_id', 'category', 'title', 'amount',
        'currency', 'expense_date', 'notes', 'receipt_image', 'is_shared',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount'       => 'float',
        'is_shared'    => 'boolean',
    ];

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getCategoryIconAttribute(): string
    {
        return match($this->category) {
            'accommodation' => 'fa-hotel',
            'food'          => 'fa-utensils',
            'transport'     => 'fa-car',
            'activities'    => 'fa-ticket',
            'shopping'      => 'fa-bag-shopping',
            'health'        => 'fa-heart-pulse',
            'communication' => 'fa-wifi',
            default         => 'fa-receipt',
        };
    }

    public function getCategoryColorAttribute(): string
    {
        return match($this->category) {
            'accommodation' => '#5A6E4D',
            'food'          => '#D35400',
            'transport'     => '#2C363F',
            'activities'    => '#D4AF37',
            'shopping'      => '#8B4513',
            'health'        => '#c0392b',
            'communication' => '#2980b9',
            default         => '#7f8c8d',
        };
    }

    public function getCategoryLabelAttribute(): string
    {
        return ucfirst($this->category);
    }
}
