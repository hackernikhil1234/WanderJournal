<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use Illuminate\Http\Request;

class DestinationController extends Controller
{
    public function index(Request $request)
    {
        $query = Destination::query();
        
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('country', 'like', "%{$search}%")
                  ->orWhere('tags', 'like', "%{$search}%");
        }
        
        if ($request->has('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }
        
        if ($request->has('continent') && $request->continent !== 'all') {
            $query->where('continent', $request->continent);
        }
        
        $destinations = $query->orderBy('name')->paginate(12)->withQueryString();
        
        $categories = Destination::select('category')->distinct()->pluck('category');
        $continents = Destination::select('continent')->distinct()->pluck('continent');
        
        return view('destinations.index', compact('destinations', 'categories', 'continents'));
    }

    public function show(Destination $destination)
    {
        $destination->load(['reviews' => function($q) {
            $q->where('is_approved', true)->latest()->take(5);
        }, 'reviews.user']);
        
        $relatedDestinations = Destination::where('category', $destination->category)
            ->where('id', '!=', $destination->id)
            ->take(3)
            ->get();
            
        return view('destinations.show', compact('destination', 'relatedDestinations'));
    }
}
