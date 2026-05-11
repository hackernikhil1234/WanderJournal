<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Models\Trip;
use App\Services\ItineraryGeneratorService;
use App\Services\GeminiAIService;
use App\Jobs\GenerateAiItineraryJob;
use App\Http\Requests\StoreTripRequest;
use App\Http\Requests\UpdateTripRequest;
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
        $aiAvailable  = app(GeminiAIService::class)->isConfigured();

        return view('trips.create', compact('destinations', 'destination', 'aiAvailable'));
    }

    public function store(StoreTripRequest $request, ItineraryGeneratorService $generator)
    {
        $validated = $request->validated();

        // Parse dates
        $dates     = explode(' to ', $validated['dates']);
        $startDate = Carbon::parse($dates[0]);
        $endDate   = count($dates) > 1 ? Carbon::parse($dates[1]) : $startDate->copy();
        $numDays   = $startDate->diffInDays($endDate) + 1;

        $trip = Trip::create([
            'user_id'                   => Auth::id(),
            'destination_id'            => $validated['destination_id'],
            'title'                     => $validated['title'],
            'start_date'                => $startDate,
            'end_date'                  => $endDate,
            'num_days'                  => $numDays,
            'budget'                    => $validated['budget'] ?? null,
            'currency'                  => $validated['currency'] ?? 'USD',
            'travel_style'              => $validated['travel_style'],
            'num_travelers'             => $validated['num_travelers'],
            'interests'                 => isset($validated['interests']) ? implode(',', $validated['interests']) : null,
            'food_preferences'          => $validated['food_preferences'] ?? null,
            'accommodation_type'        => $validated['accommodation_type'] ?? null,
            'transportation_preference' => $validated['transportation_preference'] ?? null,
            'budget_mode'               => $validated['budget_mode'] ?? 'standard',
            'notes'                     => $validated['notes'] ?? null,
            'status'                    => 'planning',
        ]);

        // Mark as generating
        $trip->ai_metadata = ['is_generating' => true];
        $trip->save();

        // Dispatch background job for itinerary generation
        GenerateAiItineraryJob::dispatch($trip);

        $successMsg = '🗺️ Your trip has been created! The AI is currently crafting your personalized itinerary. It will appear here shortly.';

        return redirect()->route('trips.itinerary.show', $trip)->with('success', $successMsg);
    }

    public function show(Trip $trip)
    {
        $this->authorize('view', $trip);

        $trip->load(['destination', 'itineraryDays.items', 'bookings', 'expenses']);
        return view('trips.show', compact('trip'));
    }

    public function edit(Trip $trip)
    {
        $this->authorize('update', $trip);
        $destinations = Destination::orderBy('name')->get();
        $aiAvailable  = app(GeminiAIService::class)->isConfigured();
        return view('trips.edit', compact('trip', 'destinations', 'aiAvailable'));
    }

    public function update(UpdateTripRequest $request, Trip $trip)
    {
        $this->authorize('update', $trip);

        $validated = $request->validated();

        $trip->update($validated);

        return redirect()->route('trips.show', $trip)->with('success', 'Trip updated successfully!');
    }

    public function destroy(Trip $trip)
    {
        $this->authorize('delete', $trip);
        $trip->delete();
        return redirect()->route('dashboard')->with('success', 'Trip deleted.');
    }

    /**
     * Clone an existing trip with new dates.
     */
    public function clone(Trip $trip, ItineraryGeneratorService $generator)
    {
        $this->authorize('view', $trip);

        $newTrip = $trip->replicate(['ai_generated', 'ai_summary', 'ai_metadata', 'estimated_cost']);
        $newTrip->title     = 'Copy of ' . $trip->title;
        $newTrip->status    = 'planning';
        $newTrip->user_id   = Auth::id();
        $newTrip->is_public = false;
        $newTrip->save();

        $newTrip->ai_metadata = ['is_generating' => true];
        $newTrip->save();

        GenerateAiItineraryJob::dispatch($newTrip);

        return redirect()->route('trips.show', $newTrip)->with('success', 'Trip cloned! The AI is regenerating your itinerary for the new dates.');
    }
}
