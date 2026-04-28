<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Models\ItineraryItem;
use App\Models\ItineraryDay;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ItineraryController extends Controller
{
    public function show(Trip $trip)
    {
        if ($trip->user_id !== Auth::id() && !$trip->is_public) abort(403);
        
        $trip->load(['itineraryDays.items' => function($query) {
            $query->orderBy('sort_order');
        }]);
        
        return view('trips.itinerary.show', compact('trip'));
    }

    public function reorder(Request $request, Trip $trip)
    {
        if ($trip->user_id !== Auth::id()) abort(403);
        
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:itinerary_items,id',
            'items.*.day_id' => 'required|exists:itinerary_days,id',
            'items.*.order' => 'required|integer',
        ]);
        
        foreach ($validated['items'] as $itemData) {
            ItineraryItem::where('id', $itemData['id'])
                ->whereHas('itineraryDay', function($query) use ($trip) {
                    $query->where('trip_id', $trip->id);
                })
                ->update([
                    'itinerary_day_id' => $itemData['day_id'],
                    'sort_order' => $itemData['order']
                ]);
        }
        
        return response()->json(['success' => true]);
    }
    
    public function storeItem(Request $request, Trip $trip, ItineraryDay $day)
    {
        if ($trip->user_id !== Auth::id()) abort(403);
        if ($day->trip_id !== $trip->id) abort(404);
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:attraction,restaurant,hotel,transport,activity,shopping,other',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'cost' => 'nullable|numeric|min:0',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);
        
        $maxOrder = $day->items()->max('sort_order') ?? 0;
        $validated['sort_order'] = $maxOrder + 1;
        $validated['itinerary_day_id'] = $day->id;
        
        ItineraryItem::create($validated);
        
        return back()->with('success', 'Item added to itinerary');
    }
    
    public function destroyItem(Trip $trip, ItineraryItem $item)
    {
        if ($trip->user_id !== Auth::id()) abort(403);
        
        $item->delete();
        
        return back()->with('success', 'Item removed');
    }
}
