@extends('layouts.app')

@section('title', 'My Bookings - WanderJournal')

@section('content')
<div class="py-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-end mb-10 border-b-2 border-journal-border pb-6">
        <div>
            <h1 class="text-4xl font-serif font-bold text-journal-dark mb-2">My Bookings</h1>
            <p class="text-journal-light">Manage your flights, hotels, and reservations.</p>
        </div>
        
        <button x-data @click="document.getElementById('add-booking-modal').classList.remove('hidden')" class="bg-journal-dark hover:bg-journal-accent text-white font-bold py-2 px-6 shadow-sm transition flex items-center gap-2 text-sm uppercase tracking-wider">
            <i class="fa-solid fa-plus"></i> Add Booking
        </button>
    </div>
    
    @if($bookings->isEmpty())
        <div class="text-center py-20 bg-journal-paper border border-journal-border rounded-sm stamp-border m-4">
            <i class="fa-solid fa-ticket-simple text-6xl text-journal-light mb-4"></i>
            <h3 class="text-xl font-serif text-journal-dark mb-2">No bookings yet</h3>
            <p class="text-journal-light">Keep track of your reservations by adding them here.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($bookings as $booking)
                <div class="bg-white border border-journal-border shadow-postcard relative group">
                    <div class="absolute -top-3 right-4 text-journal-light opacity-50 text-xl transform rotate-12 z-10"><i class="fa-solid fa-paperclip"></i></div>
                    
                    <div class="p-6">
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-journal-bg rounded-full flex items-center justify-center text-journal-dark border border-journal-border">
                                    {{ $booking->type_icon }}
                                </div>
                                <div>
                                    <h3 class="font-bold font-serif text-lg leading-tight">{{ $booking->title }}</h3>
                                    <div class="text-xs uppercase tracking-wider text-journal-light">{{ $booking->type }}</div>
                                </div>
                            </div>
                            
                            <form action="{{ route('bookings.destroy', $booking) }}" method="POST" onsubmit="return confirm('Delete this booking?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-400 hover:text-red-600 opacity-0 group-hover:opacity-100 transition"><i class="fa-solid fa-trash-can"></i></button>
                            </form>
                        </div>
                        
                        <div class="space-y-3 mb-6">
                            @if($booking->trip)
                            <div class="flex gap-3 text-sm">
                                <i class="fa-solid fa-suitcase mt-1 text-journal-olive w-4 text-center"></i>
                                <div>
                                    <span class="text-journal-light block text-xs">Associated Trip</span>
                                    <a href="{{ route('trips.show', $booking->trip) }}" class="font-medium text-journal-dark hover:text-journal-accent">{{ $booking->trip->title }}</a>
                                </div>
                            </div>
                            @endif
                            
                            @if($booking->check_in)
                            <div class="flex gap-3 text-sm">
                                <i class="fa-regular fa-calendar mt-1 text-journal-olive w-4 text-center"></i>
                                <div>
                                    <span class="text-journal-light block text-xs">Dates</span>
                                    <span class="font-medium">{{ $booking->check_in->format('M d, Y') }} @if($booking->check_out) - {{ $booking->check_out->format('M d, Y') }} @endif</span>
                                </div>
                            </div>
                            @endif
                            
                            @if($booking->provider)
                            <div class="flex gap-3 text-sm">
                                <i class="fa-solid fa-building mt-1 text-journal-olive w-4 text-center"></i>
                                <div>
                                    <span class="text-journal-light block text-xs">Provider / Reference</span>
                                    <span class="font-medium">{{ $booking->provider }} <span class="text-journal-light text-xs font-mono ml-1">{{ $booking->booking_ref }}</span></span>
                                </div>
                            </div>
                            @endif
                            
                            @if($booking->from_location)
                            <div class="flex gap-3 text-sm">
                                <i class="fa-solid fa-route mt-1 text-journal-olive w-4 text-center"></i>
                                <div>
                                    <span class="text-journal-light block text-xs">Route / Location</span>
                                    <span class="font-medium">{{ $booking->from_location }} @if($booking->to_location) <i class="fa-solid fa-arrow-right text-xs mx-1"></i> {{ $booking->to_location }} @endif</span>
                                </div>
                            </div>
                            @endif
                        </div>
                        
                        <div class="border-t border-dashed border-journal-border pt-4 flex justify-between items-center">
                            <span class="bg-{{ $booking->status_color }}-100 text-{{ $booking->status_color }}-800 border border-{{ $booking->status_color }}-200 text-xs font-bold px-2 py-1 uppercase tracking-wider">
                                {{ $booking->status }}
                            </span>
                            <span class="font-serif font-bold text-xl text-journal-dark">
                                {{ $booking->currency }} {{ number_format($booking->price, 2) }}
                            </span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<!-- Add Booking Modal -->
<div id="add-booking-modal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-journal-dark bg-opacity-75 transition-opacity" aria-hidden="true" onclick="document.getElementById('add-booking-modal').classList.add('hidden')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-journal-paper border border-journal-border text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full relative">
            <div class="absolute top-0 right-0 pt-4 pr-4">
                <button type="button" class="text-journal-light hover:text-journal-dark" onclick="document.getElementById('add-booking-modal').classList.add('hidden')">
                    <i class="fa-solid fa-times text-xl"></i>
                </button>
            </div>
            
            <form action="{{ route('bookings.store') }}" method="POST" class="p-6 sm:p-8">
                @csrf
                <div class="text-center mb-6">
                    <h3 class="text-2xl font-serif font-bold text-journal-dark">Add New Booking</h3>
                </div>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-journal-dark uppercase tracking-wider mb-1">Associated Trip *</label>
                        <select name="trip_id" required class="w-full border-journal-border bg-white focus:ring-journal-accent focus:border-journal-accent rounded-sm shadow-sm text-sm">
                            <option value="">Select a trip...</option>
                            @foreach($trips as $trip)
                                <option value="{{ $trip->id }}">{{ $trip->title }} ({{ $trip->start_date->format('M Y') }})</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-journal-dark uppercase tracking-wider mb-1">Type *</label>
                            <select name="type" required class="w-full border-journal-border bg-white focus:ring-journal-accent focus:border-journal-accent rounded-sm shadow-sm text-sm">
                                <option value="flight">✈️ Flight</option>
                                <option value="hotel">🏨 Hotel</option>
                                <option value="activity">🎯 Activity</option>
                                <option value="car_rental">🚗 Car Rental</option>
                                <option value="train">🚆 Train</option>
                                <option value="tour">🗺️ Tour</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-journal-dark uppercase tracking-wider mb-1">Price *</label>
                            <input type="number" name="price" step="0.01" min="0" required class="w-full border-journal-border bg-white focus:ring-journal-accent focus:border-journal-accent rounded-sm shadow-sm text-sm" placeholder="0.00">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold text-journal-dark uppercase tracking-wider mb-1">Title/Description *</label>
                        <input type="text" name="title" required class="w-full border-journal-border bg-white focus:ring-journal-accent focus:border-journal-accent rounded-sm shadow-sm text-sm" placeholder="E.g., Delta Flight to Paris">
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-journal-dark uppercase tracking-wider mb-1">Provider</label>
                            <input type="text" name="provider" class="w-full border-journal-border bg-white focus:ring-journal-accent focus:border-journal-accent rounded-sm shadow-sm text-sm" placeholder="Airline, Hotel chain...">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-journal-dark uppercase tracking-wider mb-1">Booking Ref/PNR</label>
                            <input type="text" name="booking_ref" class="w-full border-journal-border bg-white focus:ring-journal-accent focus:border-journal-accent rounded-sm shadow-sm text-sm text-transform uppercase">
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-journal-dark uppercase tracking-wider mb-1">Check-in / Departure</label>
                            <input type="date" name="check_in" class="w-full border-journal-border bg-white focus:ring-journal-accent focus:border-journal-accent rounded-sm shadow-sm text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-journal-dark uppercase tracking-wider mb-1">Check-out / Arrival</label>
                            <input type="date" name="check_out" class="w-full border-journal-border bg-white focus:ring-journal-accent focus:border-journal-accent rounded-sm shadow-sm text-sm">
                        </div>
                    </div>
                </div>
                
                <div class="mt-8">
                    <button type="submit" class="w-full bg-journal-dark hover:bg-journal-accent text-white font-bold py-3 uppercase tracking-wider shadow-sm transition-colors text-sm">
                        Save Booking
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
