<?php

namespace App\Policies;

use App\Models\Expense;
use App\Models\Trip;
use App\Models\User;

class ExpensePolicy
{
    /**
     * Determine whether the user can view the expenses of a trip.
     */
    public function view(User $user, Trip $trip): bool
    {
        // Viewers and Editors can view expenses
        return $trip->canBeViewedBy($user);
    }

    /**
     * Determine whether the user can create an expense.
     */
    public function create(User $user, Trip $trip): bool
    {
        return $trip->canBeEditedBy($user);
    }

    /**
     * Determine whether the user can delete an expense.
     */
    public function delete(User $user, Expense $expense): bool
    {
        // Only the creator of the expense or the trip owner can delete it
        return $expense->user_id === $user->id || $expense->trip->user_id === $user->id;
    }
}
