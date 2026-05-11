<?php

namespace App\Policies;

use App\Models\Trip;
use App\Models\User;

class TripPolicy
{
    /**
     * Determine whether the user can view the trip.
     */
    public function view(?User $user, Trip $trip): bool
    {
        if ($trip->is_public) return true;
        if (!$user) return false;
        if ($trip->user_id === $user->id) return true;
        
        return $trip->collaborators()
            ->where('user_id', $user->id)
            ->whereNotNull('accepted_at')
            ->exists();
    }

    /**
     * Determine whether the user can update the trip.
     */
    public function update(User $user, Trip $trip): bool
    {
        if ($trip->user_id === $user->id) return true;

        return $trip->collaborators()
            ->where('user_id', $user->id)
            ->where('role', 'editor')
            ->whereNotNull('accepted_at')
            ->exists();
    }

    /**
     * Determine whether the user can delete the trip.
     */
    public function delete(User $user, Trip $trip): bool
    {
        return $trip->user_id === $user->id;
    }
}
