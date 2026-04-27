@extends('layouts.app')

@section('title', 'Dashboard - WanderJournal')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    
    <!-- Welcome Header -->
    <div class="flex flex-col md:flex-row justify-between items-center mb-12 gap-6 bg-journal-dark p-8 text-white relative overflow-hidden">
        <i class="fa-solid fa-compass absolute -right-10 -bottom-10 text-[200px] opacity-10 text-journal-gold"></i>
        
        <div class="flex items-center gap-6 relative z-10">
            <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-20 h-20 rounded-full border-4 border-journal-paper shadow-sm">
            <div>
                <h1 class="text-3xl font-serif font-bold">Welcome back, {{ explode(' ', $user->name)[0] }}!</h1>
                <p class="text-gray-300 flex items-center gap-2 mt-1">
                    <i class="fa-solid fa-location-dot text-journal-accent"></i> 
                    {{ $user->country ?? 'Global Explorer' }}
                </p>
            </div>
        </div>
        
        <div class="relative z-10">
            <a href="{{ route('trips.create') }}" class="bg-journal-gold hover:bg-white text-journal-dark font-bold py-3 px-6 shadow-sm transition inline-flex items-center gap-2 uppercase tracking-wider text-sm">
                <i class="fa-solid fa-plus"></i> New Journey
            </a>
        </div>
    </div>
    
    <!-- Stats Row -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
        <div class="bg-white p-6 border border-journal-border shadow-sm flex items-center gap-4 stamp-border">
            <div class="bg-journal-bg text-journal-accent rounded-full w-12 h-12 flex items-center justify-center text-xl border border-journal-border">
                <i class="fa-solid fa-plane"></i>
            </div>
            <div>
                <div class="text-3xl font-serif font-bold text-journal-dark">{{ $stats['total_trips'] }}</div>
                <div class="text-xs uppercase tracking-wider font-bold text-journal-light">Total Trips</div>
            </div>
        </div>
        
        <div class="bg-white p-6 border border-journal-border shadow-sm flex items-center gap-4 stamp-border">
            <div class="bg-journal-bg text-journal-olive rounded-full w-12 h-12 flex items-center justify-center text-xl border border-journal-border">
                <i class="fa-solid fa-earth-americas"></i>
            </div>
            <div>
                <div class="text-3xl font-serif font-bold text-journal-dark">{{ $stats['countries_visited'] }}</div>
                <div class="text-xs uppercase tracking-wider font-bold text-journal-light">Countries</div>
            </div>
        </div>
        
        <div class="bg-white p-6 border border-journal-border shadow-sm flex items-center gap-4 stamp-border">
            <div class="bg-journal-bg text-journal-dark rounded-full w-12 h-12 flex items-center justify-center text-xl border border-journal-border">
                <i class="fa-solid fa-ticket"></i>
            </div>
            <div>
                <div class="text-3xl font-serif font-bold text-journal-dark">{{ $stats['upcoming_bookings'] }}</div>
                <div class="text-xs uppercase tracking-wider font-bold text-journal-light">Active Bookings</div>
            </div>
        </div>
    </div>

    <!-- Upcoming Trips -->
    <div class="mb-16">
        <div class="flex justify-between items-end mb-6 border-b-2 border-journal-border pb-2">
            <h2 class="text-2xl font-serif font-bold text-journal-dark">Upcoming Journeys</h2>
            <a href="{{ route('trips.index') }}" class="text-sm font-bold text-journal-olive hover:text-journal-dark uppercase tracking-wider">View All</a>
        </div>
        
        @if($upcomingTrips->isEmpty())
        <div class="bg-journal-paper p-8 text-center border border-dashed border-journal-border">
            <i class="fa-regular fa-calendar-xmark text-4xl text-journal-light mb-3"></i>
            <h3 class="text-xl font-serif text-journal-dark mb-1">No upcoming trips</h3>
            <p class="text-journal-light text-sm">Time to plan your next adventure!</p>
        </div>
        @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($upcomingTrips as $trip)
            <div class="bg-white border border-journal-border shadow-postcard overflow-hidden group">
                <div class="h-32 overflow-hidden relative">
                    <img src="{{ $trip->destination->cover_image_url }}" alt="{{ $trip->destination->name }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                    <div class="absolute bottom-3 left-3 text-white">
                        <div class="text-xs uppercase tracking-wider font-bold">{{ $trip->destination->country }}</div>
                        <h3 class="text-xl font-serif font-bold">{{ $trip->destination->name }}</h3>
                    </div>
                </div>
                
                <div class="p-5">
                    <h4 class="font-bold text-journal-dark mb-2 truncate" title="{{ $trip->title }}">{{ $trip->title }}</h4>
                    
                    <div class="flex items-center gap-2 text-sm text-journal-light mb-4">
                        <i class="fa-regular fa-calendar"></i>
                        <span>{{ $trip->start_date->format('M d') }} - {{ $trip->end_date->format('M d, Y') }}</span>
                    </div>
                    
                    <!-- Progress Bar -->
                    <div class="mb-5">
                        <div class="flex justify-between text-xs font-bold uppercase tracking-wider mb-1">
                            <span class="text-journal-light">Packing</span>
                            <span class="text-journal-dark">{{ $trip->packing_progress }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-1.5">
                            <div class="bg-journal-olive h-1.5 rounded-full" style="width: {{ $trip->packing_progress }}%"></div>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-2">
                        <a href="{{ route('trips.itinerary.show', $trip) }}" class="text-center bg-journal-paper hover:bg-journal-dark hover:text-white border border-journal-border text-journal-dark py-2 text-xs font-bold uppercase tracking-wider transition">
                            Itinerary
                        </a>
                        <a href="{{ route('trips.packing.show', $trip) }}" class="text-center bg-journal-paper hover:bg-journal-dark hover:text-white border border-journal-border text-journal-dark py-2 text-xs font-bold uppercase tracking-wider transition">
                            Packing
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

</div>
@endsection
