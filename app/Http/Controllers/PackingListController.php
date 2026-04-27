<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Models\PackingItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PackingListController extends Controller
{
    public function show(Trip $trip)
    {
        if ($trip->user_id !== Auth::id()) abort(403);
        
        $items = $trip->packingItems()
            ->orderBy('category')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('category');
            
        return view('trips.packing.show', compact('trip', 'items'));
    }

    public function store(Request $request, Trip $trip)
    {
        if ($trip->user_id !== Auth::id()) abort(403);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|in:clothing,essentials,electronics,toiletries,documents,health,entertainment,other',
            'quantity' => 'required|integer|min:1',
        ]);
        
        $maxOrder = $trip->packingItems()->where('category', $validated['category'])->max('sort_order') ?? 0;
        $validated['sort_order'] = $maxOrder + 1;
        
        $trip->packingItems()->create($validated);
        
        return back()->with('success', 'Item added to packing list');
    }

    public function toggle(Request $request, Trip $trip, PackingItem $item)
    {
        if ($trip->user_id !== Auth::id()) abort(403);
        if ($item->trip_id !== $trip->id) abort(404);
        
        $item->update(['is_packed' => !$item->is_packed]);
        
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true, 
                'is_packed' => $item->is_packed,
                'progress' => $trip->packing_progress
            ]);
        }
        
        return back();
    }
    
    public function destroy(Trip $trip, PackingItem $item)
    {
        if ($trip->user_id !== Auth::id()) abort(403);
        $item->delete();
        return back()->with('success', 'Item removed');
    }
}
