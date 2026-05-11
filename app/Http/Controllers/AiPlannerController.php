<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Services\GeminiAIService;
use App\Services\ItineraryGeneratorService;
use App\Jobs\GenerateAiItineraryJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class AiPlannerController extends Controller
{
    public function __construct(
        private GeminiAIService $ai,
        private ItineraryGeneratorService $generator
    ) {}

    /**
     * Regenerate the AI itinerary for an existing trip.
     */
    public function regenerate(Trip $trip)
    {
        if (!$trip->canBeEditedBy(Auth::user())) abort(403);

        // Clear old items and mark as generating
        $trip->itineraryDays()->delete();
        $meta = $trip->ai_metadata ?? [];
        $meta['is_generating'] = true;
        unset($meta['generation_failed']);
        $trip->ai_metadata = $meta;
        $trip->save();

        GenerateAiItineraryJob::dispatch($trip);
        
        // Clear caches so they get regenerated with the new itinerary context
        Cache::forget("trip_{$trip->id}_tips");
        Cache::forget("trip_{$trip->id}_hotels");
        Cache::forget("trip_{$trip->id}_budget");

        return redirect()->route('trips.itinerary.show', $trip)
            ->with('success', '✨ AI is regenerating your itinerary. It will be ready in a moment!');
    }

    /**
     * Get AI travel tips for a trip (AJAX).
     */
    public function getTravelTips(Trip $trip)
    {
        if (!$trip->canBeViewedBy(Auth::user())) abort(403);

        // Return cached AI tips from metadata
        $tips = $trip->ai_tips;

        if (empty($tips) && $this->ai->isConfigured()) {
            $tips = Cache::remember("trip_{$trip->id}_tips", 86400, function () use ($trip) {
                return $this->ai->generateTravelTips($trip)['best_time_tips'] ?? [];
            });
        }

        return response()->json(['tips' => $tips, 'ai_available' => $this->ai->isConfigured()]);
    }

    /**
     * Get AI hotel recommendations (AJAX).
     */
    public function getHotelRecommendations(Trip $trip)
    {
        if (!$trip->canBeViewedBy(Auth::user())) abort(403);

        $recommendations = Cache::remember("trip_{$trip->id}_hotels", 86400, function () use ($trip) {
            return $this->ai->generateHotelRecommendations($trip);
        });

        return response()->json([
            'recommendations' => $recommendations,
            'ai_available'    => $this->ai->isConfigured(),
        ]);
    }

    /**
     * Get AI budget breakdown for a trip (AJAX).
     */
    public function getBudgetBreakdown(Trip $trip)
    {
        if (!$trip->canBeViewedBy(Auth::user())) abort(403);

        $breakdown = Cache::remember("trip_{$trip->id}_budget", 86400, function () use ($trip) {
            return $this->ai->generateBudgetBreakdown($trip);
        });

        return response()->json([
            'breakdown'   => $breakdown,
            'ai_available' => $this->ai->isConfigured(),
        ]);
    }

    /**
     * Chat with the AI travel assistant (AJAX).
     */
    public function chat(Request $request)
    {
        $request->validate([
            'message'  => 'required|string|max:500',
            'trip_id'  => 'nullable|exists:trips,id',
        ]);

        $context = [];
        if ($request->trip_id) {
            $trip = Trip::with('destination')->find($request->trip_id);
            if ($trip && $trip->canBeViewedBy(Auth::user())) {
                $context['trip'] = [
                    'title'       => $trip->title,
                    'destination' => $trip->destination->name . ', ' . $trip->destination->country,
                    'dates'       => $trip->start_date->format('M d') . ' - ' . $trip->end_date->format('M d, Y'),
                    'style'       => $trip->travel_style,
                ];
            }
        }

        if (Auth::check()) {
            $context['user'] = [
                'name'  => Auth::user()->name,
                'style' => Auth::user()->travel_style ?? 'explorer',
            ];
        }

        $response = $this->ai->chat($request->message, $context);

        return response()->json(['response' => $response, 'ai_available' => $this->ai->isConfigured()]);
    }

    /**
     * Check if the AI itinerary generation is still running.
     */
    public function getGenerationStatus(Trip $trip)
    {
        if (!$trip->canBeViewedBy(Auth::user())) abort(403);

        $meta = $trip->ai_metadata ?? [];
        $isGenerating = $meta['is_generating'] ?? false;
        $hasFailed = $meta['generation_failed'] ?? false;

        return response()->json([
            'is_generating' => $isGenerating,
            'has_failed'    => $hasFailed,
            'is_ready'      => !$isGenerating && !$hasFailed,
        ]);
    }
}
