<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $trips = $user->trips()
            ->with(['destination', 'bookings', 'expenses'])
            ->orderBy('start_date', 'asc')
            ->get();

        $upcomingTrips = $trips->filter(fn($t) => $t->end_date >= now() && $t->status !== 'cancelled');
        $pastTrips     = $trips->filter(fn($t) => $t->end_date < now() || $t->status === 'completed');

        // Core stats
        $totalSpent       = $trips->sum('total_expenses');
        $totalDaysTraveled = $pastTrips->sum('num_days');
        $countriesVisited = $pastTrips->pluck('destination.country')->filter()->unique()->values();

        $stats = [
            'total_trips'       => $trips->count(),
            'countries_visited' => $countriesVisited->count(),
            'upcoming_bookings' => $user->bookings()->where('check_in', '>=', now())->count(),
            'total_spent'       => round($totalSpent, 2),
            'days_traveled'     => $totalDaysTraveled,
            'ai_trips'          => $trips->where('ai_generated', true)->count(),
        ];

        // Upcoming alerts (trip starting within 7 days)
        $tripAlerts = $upcomingTrips
            ->filter(fn($t) => $t->days_until_trip <= 7 && $t->days_until_trip >= 0)
            ->values();

        // Monthly expense trend (last 6 months)
        $monthlyExpenses = $user->expenses()
            ->selectRaw("DATE_FORMAT(expense_date, '%Y-%m') as month, SUM(amount) as total")
            ->where('expense_date', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(fn($row) => ['month' => $row->month, 'total' => $row->total]);

        // Category breakdown (all time)
        $categoryBreakdown = $user->expenses()
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->get()
            ->map(fn($row) => ['category' => $row->category, 'total' => $row->total]);

        // Countries for map visualization
        $countriesData = $countriesVisited->toArray();

        return view('dashboard', compact(
            'user', 'upcomingTrips', 'pastTrips', 'stats',
            'tripAlerts', 'monthlyExpenses', 'categoryBreakdown', 'countriesData'
        ));
    }
}
