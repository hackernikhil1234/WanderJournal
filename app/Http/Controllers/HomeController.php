<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Models\Trip;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $featuredDestinations = Destination::featured()->take(6)->get();
        $popularDestinations = Destination::orderByDesc('popularity_score')->take(4)->get();
        
        $stats = [
            'users' => \App\Models\User::count() + 1542,
            'trips' => \App\Models\Trip::count() + 8430,
            'destinations' => \App\Models\Destination::count(),
        ];
        
        return view('home', compact('featuredDestinations', 'popularDestinations', 'stats'));
    }
}
