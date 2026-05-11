<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password',
        'avatar', 'phone', 'country', 'bio',
        'travel_style', 'interests', 'default_budget', 'currency',
        'total_money_spent', 'total_days_traveled', 'visited_countries',
        'badges', 'preferred_language', 'dark_mode', 'last_active_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at'   => 'datetime',
            'password'            => 'hashed',
            'default_budget'      => 'float',
            'total_money_spent'   => 'float',
            'total_days_traveled' => 'integer',
            'visited_countries'   => 'array',
            'badges'              => 'array',
            'dark_mode'           => 'boolean',
            'last_active_at'      => 'datetime',
        ];
    }

    // === Core Relationships ===

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    // === Social Relationships ===

    public function travelPosts(): HasMany
    {
        return $this->hasMany(TravelPost::class);
    }

    public function following(): HasMany
    {
        return $this->hasMany(Follow::class, 'follower_id');
    }

    public function followers(): HasMany
    {
        return $this->hasMany(Follow::class, 'following_id');
    }

    public function collaboratedTrips(): BelongsToMany
    {
        return $this->belongsToMany(Trip::class, 'trip_collaborators', 'user_id', 'trip_id')
            ->withPivot('role', 'accepted_at')
            ->whereNotNull('trip_collaborators.accepted_at');
    }

    // === Accessors ===

    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar && str_starts_with($this->avatar, 'http')) {
            return $this->avatar;
        }
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }
        $name = urlencode($this->name);
        return "https://ui-avatars.com/api/?name={$name}&background=8B4513&color=fff&size=150&font-size=0.4";
    }

    public function getInterestsArrayAttribute(): array
    {
        return $this->interests ? explode(',', $this->interests) : [];
    }

    public function isFollowing(User $user): bool
    {
        return $this->following()->where('following_id', $user->id)->exists();
    }

    public function getFollowersCountAttribute(): int
    {
        return $this->followers()->count();
    }

    public function getFollowingCountAttribute(): int
    {
        return $this->following()->count();
    }

    public function getCountriesVisitedCountAttribute(): int
    {
        return count($this->visited_countries ?? []);
    }
}
