<?php

namespace App\Observers;

use App\Models\Expense;
use Illuminate\Support\Facades\Cache;

class ExpenseObserver
{
    /**
     * Clear the trip analytics cache.
     */
    private function clearCache(Expense $expense): void
    {
        Cache::forget("trip_{$expense->trip_id}_analytics");
    }

    /**
     * Handle the Expense "created" event.
     */
    public function created(Expense $expense): void
    {
        $this->clearCache($expense);
    }

    /**
     * Handle the Expense "updated" event.
     */
    public function updated(Expense $expense): void
    {
        $this->clearCache($expense);
    }

    /**
     * Handle the Expense "deleted" event.
     */
    public function deleted(Expense $expense): void
    {
        $this->clearCache($expense);
    }

    /**
     * Handle the Expense "restored" event.
     */
    public function restored(Expense $expense): void
    {
        $this->clearCache($expense);
    }

    /**
     * Handle the Expense "force deleted" event.
     */
    public function forceDeleted(Expense $expense): void
    {
        $this->clearCache($expense);
    }
}
