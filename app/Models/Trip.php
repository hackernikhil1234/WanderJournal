<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Trip extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'destination_id', 'title', 'start_date', 'end_date',
        'num_days', 'budget', 'currency', 'travel_style', 'interests',
        'num_travelers', 'status', 'notes', 'estimated_cost', 'is_public',
        // AI fields
        'ai_generated', 'ai_summary', 'food_preferences',
        'accommodation_type', 'transportation_preference',
        'budget_mode', 'ai_metadata',
    ];

    protected $casts = [
        'start_date'    => 'date',
        'end_date'      => 'date',
        'budget'        => 'float',
        'estimated_cost' => 'float',
        'is_public'     => 'boolean',
        'ai_generated'  => 'boolean',
        'ai_metadata'   => 'array',
    ];

    // === Relationships ===

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

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function travelPosts(): HasMany
    {
        return $this->hasMany(TravelPost::class);
    }

    public function collaborators(): HasMany
    {
        return $this->hasMany(TripCollaborator::class);
    }

    public function collaboratorUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'trip_collaborators', 'trip_id', 'user_id')
            ->withPivot('role', 'accepted_at')
            ->whereNotNull('trip_collaborators.accepted_at');
    }

    // === Computed Attributes ===

    public function getTotalCostAttribute(): float
    {
        return $this->itineraryDays->flatMap->items->sum('cost') ?? 0;
    }

    public function getTotalExpensesAttribute(): float
    {
        return $this->expenses()->sum('amount');
    }

    public function getPackingProgressAttribute(): int
    {
        $total = $this->packingItems()->count();
        if ($total === 0) return 0;
        $packed = $this->packingItems()->where('is_packed', true)->count();
        return (int) round(($packed / $total) * 100);
    }

    public function getBudgetUsedPercentAttribute(): float
    {
        if (!$this->budget || $this->budget == 0) return 0;
        return min(100, round(($this->total_expenses / $this->budget) * 100, 1));
    }

    public function getDaysUntilTripAttribute(): int
    {
        return max(0, now()->diffInDays($this->start_date, false));
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return match($this->status) {
            'planning'  => 'yellow',
            'confirmed' => 'green',
            'completed' => 'blue',
            'cancelled' => 'red',
            default     => 'gray',
        };
    }

    public function getAiTipsAttribute(): array
    {
        $meta = $this->ai_metadata ?? [];
        return $meta['top_tips'] ?? [];
    }

    public function getBestRestaurantsAttribute(): array
    {
        $meta = $this->ai_metadata ?? [];
        return $meta['best_restaurants'] ?? [];
    }

    // === Scopes ===

    public function scopeUpcoming($query)
    {
        return $query->where('end_date', '>=', now())->where('status', '!=', 'cancelled');
    }

    public function scopePast($query)
    {
        return $query->where('end_date', '<', now())->orWhere('status', 'completed');
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    // === Helpers ===

    public function canBeEditedBy(User $user): bool
    {
        if ($this->user_id === $user->id) return true;

        return $this->collaborators()
            ->where('user_id', $user->id)
            ->where('role', 'editor')
            ->whereNotNull('accepted_at')
            ->exists();
    }

    public function canBeViewedBy(?User $user): bool
    {
        if ($this->is_public) return true;
        if (!$user) return false;
        if ($this->user_id === $user->id) return true;
        return $this->collaborators()->where('user_id', $user->id)->whereNotNull('accepted_at')->exists();
    }
}
