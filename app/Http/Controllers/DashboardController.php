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
            ->with(['destination', 'bookings'])
            ->orderBy('start_date', 'asc')
            ->get();
            
        $upcomingTrips = $trips->filter(function($trip) {
            return $trip->end_date >= now() && $trip->status !== 'cancelled';
        });
        
        $pastTrips = $trips->filter(function($trip) {
            return $trip->end_date < now() || $trip->status === 'completed';
        });
        
        $stats = [
            'total_trips' => $trips->count(),
            'countries_visited' => $pastTrips->pluck('destination.country')->unique()->count(),
            'upcoming_bookings' => $user->bookings()->where('check_in', '>=', now())->count(),
        ];
        
        return view('dashboard', compact('user', 'upcomingTrips', 'pastTrips', 'stats'));
    }
}
