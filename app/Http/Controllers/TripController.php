<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Models\Trip;
use App\Services\ItineraryGeneratorService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TripController extends Controller
{
    public function index()
    {
        $trips = Auth::user()->trips()->with('destination')->latest()->get();
        return view('trips.index', compact('trips'));
    }

    public function create(Request $request)
    {
        $destination = null;
        if ($request->has('destination')) {
            $destination = Destination::where('slug', $request->destination)->first();
        }
        
        $destinations = Destination::orderBy('name')->get();
        
        return view('trips.create', compact('destinations', 'destination'));
    }

    public function store(Request $request, ItineraryGeneratorService $generator)
    {
        $validated = $request->validate([
            'destination_id' => 'required|exists:destinations,id',
            'title' => 'required|string|max:255',
            'dates' => 'required|string', // Format: "YYYY-MM-DD to YYYY-MM-DD"
            'budget' => 'nullable|numeric|min:0',
            'travel_style' => 'required|in:luxury,budget,adventure,cultural,backpacker,family',
            'num_travelers' => 'required|integer|min:1|max:20',
            'interests' => 'nullable|array',
        ]);
        
        // Parse dates
        $dates = explode(' to ', $validated['dates']);
        $startDate = Carbon::parse($dates[0]);
        $endDate = count($dates) > 1 ? Carbon::parse($dates[1]) : clone $startDate;
        $numDays = $startDate->diffInDays($endDate) + 1;
        
        $trip = Trip::create([
            'user_id' => Auth::id(),
            'destination_id' => $validated['destination_id'],
            'title' => $validated['title'],
            'start_date' => $startDate,
            'end_date' => $endDate,
            'num_days' => $numDays,
            'budget' => $validated['budget'] ?? null,
            'travel_style' => $validated['travel_style'],
            'num_travelers' => $validated['num_travelers'],
            'interests' => isset($validated['interests']) ? implode(',', $validated['interests']) : null,
            'status' => 'planning',
        ]);
        
        // Generate Smart Itinerary
        $generator->generateForTrip($trip);
        
        return redirect()->route('trips.itinerary.show', $trip)
            ->with('success', 'Trip created successfully! Here is your smart itinerary.');
    }

    public function show(Trip $trip)
    {
        if ($trip->user_id !== Auth::id() && !$trip->is_public) {
            abort(403);
        }
        
        $trip->load(['destination', 'itineraryDays.items', 'bookings']);
        return view('trips.show', compact('trip'));
    }

    public function edit(Trip $trip)
    {
        if ($trip->user_id !== Auth::id()) abort(403);
        $destinations = Destination::orderBy('name')->get();
        return view('trips.edit', compact('trip', 'destinations'));
    }

    public function update(Request $request, Trip $trip)
    {
        if ($trip->user_id !== Auth::id()) abort(403);
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'budget' => 'nullable|numeric|min:0',
            'travel_style' => 'required|in:luxury,budget,adventure,cultural,backpacker,family',
            'num_travelers' => 'required|integer|min:1',
            'status' => 'required|in:planning,confirmed,completed,cancelled',
            'notes' => 'nullable|string',
            'is_public' => 'boolean',
        ]);
        
        $trip->update($validated);
        
        return redirect()->route('trips.show', $trip)->with('success', 'Trip updated successfully!');
    }

    public function destroy(Trip $trip)
    {
        if ($trip->user_id !== Auth::id()) abort(403);
        $trip->delete();
        return redirect()->route('dashboard')->with('success', 'Trip deleted.');
    }
}
