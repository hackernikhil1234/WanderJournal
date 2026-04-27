@extends('layouts.app')

@section('title', 'Destinations - WanderJournal')

@section('content')
<div class="py-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col md:flex-row justify-between items-end mb-10 gap-6 border-b-2 border-journal-border pb-6">
        <div>
            <h1 class="text-5xl font-serif font-bold text-journal-dark mb-2">Explore Destinations</h1>
            <p class="text-journal-light text-lg">Discover your next great adventure from our curated global collection.</p>
        </div>
        
        <form action="{{ route('destinations.index') }}" method="GET" class="flex gap-4 w-full md:w-auto">
            <div class="relative bg-white border border-journal-border rounded-sm shadow-sm flex items-center px-3">
                <i class="fa-solid fa-search text-gray-400"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search places..." class="border-none focus:ring-0 w-full md:w-48 text-sm">
            </div>
            
            <select name="category" class="border border-journal-border rounded-sm text-sm focus:ring-journal-accent focus:border-journal-accent bg-white shadow-sm" onchange="this.form.submit()">
                <option value="all">All Categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category }}" {{ request('category') === $category ? 'selected' : '' }}>
                        {{ ucfirst($category) }}
                    </option>
                @endforeach
            </select>
            
            <button type="submit" class="bg-journal-dark text-white px-4 py-2 text-sm hover:bg-journal-accent transition-colors">
                Filter
            </button>
            @if(request()->hasAny(['search', 'category', 'continent']))
                <a href="{{ route('destinations.index') }}" class="flex items-center text-journal-light hover:text-journal-accent text-sm">
                    Clear
                </a>
            @endif
        </form>
    </div>

    @if($destinations->isEmpty())
        <div class="text-center py-20 bg-journal-paper border border-journal-border rounded-sm stamp-border m-4">
            <i class="fa-regular fa-compass text-6xl text-journal-light mb-4"></i>
            <h3 class="text-2xl font-serif text-journal-dark mb-2">No destinations found</h3>
            <p class="text-journal-light">Try adjusting your search or filters to find what you're looking for.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($destinations as $dest)
                <a href="{{ route('destinations.show', $dest) }}" class="group bg-white border border-journal-border shadow-postcard hover:shadow-lg transition-all duration-300 relative">
                    <!-- Photo -->
                    <div class="h-48 overflow-hidden bg-gray-200 border-b border-journal-border relative">
                        <img src="{{ $dest->cover_image_url }}" alt="{{ $dest->name }}" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                        <div class="absolute top-2 right-2 bg-white/90 backdrop-blur px-2 py-1 text-xs font-bold uppercase tracking-wider shadow-sm flex items-center gap-1">
                            <i class="fa-solid fa-star text-journal-gold"></i> {{ number_format($dest->average_rating, 1) }}
                        </div>
                    </div>
                    
                    <!-- Content -->
                    <div class="p-5">
                        <div class="text-xs uppercase tracking-widest text-journal-accent mb-1 font-bold">{{ $dest->country }}</div>
                        <h3 class="text-xl font-serif font-bold text-journal-dark mb-2 group-hover:text-journal-olive transition-colors">{{ $dest->name }}</h3>
                        
                        <div class="flex flex-wrap gap-2 mb-4">
                            <span class="inline-block bg-journal-paper text-journal-dark text-xs px-2 py-1 border border-journal-border rounded-sm">
                                {{ ucfirst($dest->category) }}
                            </span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
        
        <div class="mt-12 font-serif">
            {{ $destinations->links() }}
        </div>
    @endif
</div>
@endsection
