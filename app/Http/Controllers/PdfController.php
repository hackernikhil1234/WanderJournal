<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PdfController extends Controller
{
    public function exportItinerary(Trip $trip)
    {
        if ($trip->user_id !== Auth::id() && !$trip->is_public) abort(403);
        
        $trip->load(['destination', 'user', 'itineraryDays.items' => function($q) {
            $q->orderBy('sort_order');
        }]);
        
        $pdf = Pdf::loadView('pdf.itinerary', compact('trip'));
        
        // Optional: set paper size and orientation
        $pdf->setPaper('a4', 'portrait');
        
        $filename = 'WanderJournal_' . str_replace(' ', '_', $trip->destination->name) . '_Itinerary.pdf';
        
        return $pdf->download($filename);
    }
}
