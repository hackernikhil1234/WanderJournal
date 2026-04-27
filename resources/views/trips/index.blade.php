@extends('layouts.app')

@section('title', 'My Trips - WanderJournal')

@section('content')
<div class="py-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-end mb-10 border-b-2 border-journal-border pb-6">
        <div>
            <h1 class="text-4xl font-serif font-bold text-journal-dark mb-2">My Trips</h1>
            <p class="text-journal-light">All your planned and past journeys in one place.</p>
        </div>
        
        <a href="{{ route('trips.create') }}" class="bg-journal-dark hover:bg-journal-accent text-white font-bold py-2 px-6 shadow-sm transition flex items-center gap-2 text-sm uppercase tracking-wider">
            <i class="fa-solid fa-plus"></i> New Trip
        </a>
    </div>

    @if($trips->isEmpty())
        <div class="text-center py-20 bg-journal-paper border border-journal-border rounded-sm stamp-border m-4">
            <i class="fa-solid fa-map-location-dot text-6xl text-journal-light mb-4"></i>
            <h3 class="text-2xl font-serif text-journal-dark mb-2">No trips planned yet</h3>
            <p class="text-journal-light mb-6">Start your first adventure by creating a new trip.</p>
            <a href="{{ route('trips.create') }}" class="inline-block bg-journal-accent text-white font-bold py-3 px-8 shadow-sm hover:bg-journal-dark transition">
                Plan My First Trip
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($trips as $trip)
                <div class="bg-white border border-journal-border shadow-postcard group flex flex-col relative overflow-hidden">
                    <!-- Status Ribbon -->
                    <div class="absolute top-4 -right-12 bg-{{ $trip->status === 'completed' ? 'journal-olive' : ($trip->status === 'cancelled' ? 'red-600' : 'journal-accent') }} text-white text-xs font-bold uppercase tracking-wider py-1 px-12 rotate-45 z-10 shadow-sm text-center">
                        {{ $trip->status }}
                    </div>

                    <!-- Image -->
                    <div class="h-48 overflow-hidden relative">
                        <img src="{{ $trip->destination->cover_image_url }}" alt="{{ $trip->destination->name }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-700">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
                        <div class="absolute bottom-4 left-4 text-white">
                            <div class="text-xs uppercase tracking-widest font-bold text-journal-gold">{{ $trip->destination->country }}</div>
                            <h3 class="text-2xl font-serif font-bold">{{ $trip->destination->name }}</h3>
                        </div>
                    </div>
                    
                    <!-- Content -->
                    <div class="p-6 flex-grow flex flex-col">
                        <h4 class="font-bold text-journal-dark mb-4 text-xl line-clamp-1" title="{{ $trip->title }}">{{ $trip->title }}</h4>
                        
                        <div class="space-y-3 mb-6 flex-grow">
                            <div class="flex items-start gap-3 text-sm">
                                <i class="fa-regular fa-calendar text-journal-olive mt-1 w-4 text-center"></i>
                                <div>
                                    <span class="text-journal-dark font-medium">{{ $trip->start_date->format('M d, Y') }} - {{ $trip->end_date->format('M d, Y') }}</span>
                                    <span class="text-journal-light block text-xs mt-0.5">{{ $trip->num_days }} Days &bull; {{ $trip->num_travelers }} Traveler(s)</span>
                                </div>
                            </div>
                            
                            <div class="flex items-start gap-3 text-sm">
                                <i class="fa-solid fa-compass text-journal-olive mt-1 w-4 text-center"></i>
                                <span class="text-journal-dark font-medium capitalize">{{ $trip->travel_style }} Style</span>
                            </div>
                        </div>
                        
                        <!-- Actions -->
                        <div class="grid grid-cols-2 gap-2 mt-auto border-t border-journal-border pt-4">
                            <a href="{{ route('trips.itinerary.show', $trip) }}" class="text-center bg-journal-dark hover:bg-journal-accent text-white font-bold py-2 text-xs uppercase tracking-wider transition">
                                <i class="fa-solid fa-list-ul mr-1"></i> Itinerary
                            </a>
                            <a href="{{ route('trips.packing.show', $trip) }}" class="text-center bg-journal-paper hover:bg-journal-olive hover:text-white border border-journal-border text-journal-dark font-bold py-2 text-xs uppercase tracking-wider transition">
                                <i class="fa-solid fa-suitcase mr-1"></i> Packing
                            </a>
                        </div>
                        <div class="mt-2 text-center">
                            <form action="{{ route('trips.destroy', $trip) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this trip? This action cannot be undone.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs text-red-500 hover:text-red-700 underline transition">Delete Trip</button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
