<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Trip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $query = Auth::user()->bookings()->with('trip');
        
        if ($request->has('trip_id')) {
            $query->where('trip_id', $request->trip_id);
            $trip = Trip::findOrFail($request->trip_id);
            if ($trip->user_id !== Auth::id()) abort(403);
        }
        
        $bookings = $query->orderBy('check_in', 'asc')->get();
        $trips = Auth::user()->trips()->orderBy('title')->get();
        
        return view('bookings.index', compact('bookings', 'trips'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'trip_id' => 'required|exists:trips,id',
            'type' => 'required|in:flight,hotel,activity,car_rental,cruise,tour',
            'title' => 'required|string|max:255',
            'provider' => 'nullable|string|max:255',
            'booking_ref' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'check_in' => 'nullable|date',
            'check_out' => 'nullable|date|after_or_equal:check_in',
            'from_location' => 'nullable|string|max:255',
            'to_location' => 'nullable|string|max:255',
            'details' => 'nullable|string',
        ]);
        
        $trip = Trip::findOrFail($validated['trip_id']);
        if ($trip->user_id !== Auth::id()) abort(403);
        
        $validated['user_id'] = Auth::id();
        $validated['currency'] = $trip->currency;
        
        Booking::create($validated);
        
        return back()->with('success', 'Booking added successfully');
    }

    public function destroy(Booking $booking)
    {
        if ($booking->user_id !== Auth::id()) abort(403);
        $booking->delete();
        return back()->with('success', 'Booking deleted');
    }
}
