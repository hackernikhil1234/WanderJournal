<?php

namespace App\Services;

use App\Models\Trip;
use Illuminate\Support\Facades\Cache;

class BudgetOptimizationService
{
    public function __construct(private CurrencyService $currency) {}

    /**
     * Analyze spending and compare to budget, returning alerts and stats.
     */
    public function analyzeSpendings(Trip $trip): array
    {
        $expenses = $trip->expenses()->get();
        $totalSpent = $expenses->sum('amount');
        $budget = $trip->budget ?? 0;

        $byCategory = $expenses->groupBy('category')->map(function ($items, $category) {
            return [
                'category' => $category,
                'total' => $items->sum('amount'),
                'count' => $items->count(),
                'icon' => $this->getCategoryIcon($category),
                'color' => $this->getCategoryColor($category),
            ];
        })->values()->toArray();

        $daysElapsed = max(1, now()->diffInDays($trip->start_date));
        $dailyAverage = $totalSpent / $daysElapsed;
        $projectedTotal = $dailyAverage * $trip->num_days;

        $alerts = [];
        if ($budget > 0) {
            $percentUsed = ($totalSpent / $budget) * 100;
            if ($percentUsed >= 90) {
                $alerts[] = ['type' => 'danger', 'message' => "You've used {$percentUsed}% of your budget!"];
            } elseif ($percentUsed >= 75) {
                $alerts[] = ['type' => 'warning', 'message' => "Budget alert: {$percentUsed}% spent."];
            }
            if ($projectedTotal > $budget) {
                $over = round($projectedTotal - $budget, 2);
                $alerts[] = ['type' => 'warning', 'message' => "At current pace, you'll exceed budget by {$trip->currency} {$over}."];
            }
        }

        return [
            'total_spent' => round($totalSpent, 2),
            'budget' => $budget,
            'remaining' => round($budget - $totalSpent, 2),
            'percentage_used' => $budget > 0 ? round(($totalSpent / $budget) * 100, 1) : 0,
            'daily_average' => round($dailyAverage, 2),
            'projected_total' => round($projectedTotal, 2),
            'by_category' => $byCategory,
            'alerts' => $alerts,
            'currency' => $trip->currency,
        ];
    }

    /**
     * Get budget-friendly mode recommendations.
     */
    public function getBudgetFriendlyTips(Trip $trip): array
    {
        $destination = $trip->destination;

        return [
            'transport' => [
                'title' => 'Cheapest Transport Options',
                'options' => [
                    ['mode' => 'Public Bus/Metro', 'savings' => '60-70%', 'tip' => "Use local transit apps for {$destination->name}"],
                    ['mode' => 'Shared Taxi/Rideshare', 'savings' => '40-50%', 'tip' => 'Split costs with other travelers'],
                    ['mode' => 'Walking/Cycling', 'savings' => '100%', 'tip' => 'Most city centers are walkable'],
                ],
            ],
            'accommodation' => [
                'title' => 'Budget Stay Options',
                'options' => [
                    ['type' => 'Hostel Dorm', 'price_range' => '$15-40/night', 'tip' => 'Great for solo travelers'],
                    ['type' => 'Guesthouse', 'price_range' => '$30-80/night', 'tip' => 'More privacy, local feel'],
                    ['type' => 'Airbnb / Apartment', 'price_range' => '$50-120/night', 'tip' => 'Best for groups'],
                ],
            ],
            'food' => [
                'title' => 'Eat Well, Spend Less',
                'tips' => [
                    'Eat where locals eat — street food and markets are cheaper',
                    'Have your main meal at lunch (lunch menus are cheaper)',
                    'Buy snacks and drinks at supermarkets, not tourist spots',
                    'Look for "set menu" or "menu del dia" deals',
                ],
            ],
            'activities' => [
                'title' => 'Free & Budget Activities',
                'tips' => [
                    'Most museums have free days or hours',
                    'Walking tours (tip-based) are a great way to explore',
                    'Parks, beaches, and markets are free',
                    'City cards often provide unlimited transport + museum access',
                ],
            ],
            'estimated_savings' => $this->calculateBudgetSavings($trip),
        ];
    }

    /**
     * Calculate estimated savings with budget-friendly options.
     */
    private function calculateBudgetSavings(Trip $trip): array
    {
        $standardCost = ($trip->destination->avg_daily_budget ?? 150) * $trip->num_days * $trip->num_travelers;
        $budgetCost = $standardCost * 0.45; // ~55% savings

        return [
            'standard_estimate' => round($standardCost, 2),
            'budget_estimate' => round($budgetCost, 2),
            'potential_savings' => round($standardCost - $budgetCost, 2),
            'savings_percentage' => 55,
        ];
    }

    /**
     * Get expense category icon (Font Awesome class).
     */
    public function getCategoryIcon(string $category): string
    {
        return match($category) {
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

    /**
     * Get expense category color (CSS color).
     */
    public function getCategoryColor(string $category): string
    {
        return match($category) {
            'accommodation' => '#5A6E4D',  // olive
            'food'          => '#D35400',  // terracotta
            'transport'     => '#2C363F',  // dark
            'activities'    => '#D4AF37',  // gold
            'shopping'      => '#8B4513',  // brown
            'health'        => '#c0392b',  // red
            'communication' => '#2980b9',  // blue
            default         => '#7f8c8d',  // gray
        ];
    }
}
