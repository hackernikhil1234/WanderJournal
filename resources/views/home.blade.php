@extends('layouts.app')

@section('title', 'WanderJournal - Plan Your Next Great Adventure')

@section('content')
<!-- Hero Section -->
<div class="relative bg-journal-dark overflow-hidden h-[90vh] flex items-center">
    <div class="absolute inset-0 z-0">
        <img src="https://images.unsplash.com/photo-1488085061387-422e29b40080?w=1600&auto=format&fit=crop" alt="Vintage map and journal" class="w-full h-full object-cover opacity-40 mix-blend-overlay">
    </div>
    
    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full text-center">
        <span class="font-script text-3xl md:text-5xl text-journal-gold block mb-4 rotate-[-2deg]">Begin your story...</span>
        <h1 class="text-5xl md:text-7xl font-serif font-bold text-white mb-6 tracking-wide drop-shadow-lg">
            Craft Your Perfect <br><span class="italic text-journal-paper">Journey</span>
        </h1>
        <p class="mt-4 max-w-2xl text-xl text-journal-border mx-auto mb-10 font-light">
            A beautiful, smart travel planner for the modern explorer. Turn your dreams into day-by-day itineraries instantly.
        </p>
        
        <div class="bg-journal-paper/95 p-6 rounded-sm shadow-photo max-w-4xl mx-auto border-4 border-white transform transition hover:-translate-y-1 duration-300">
            <form action="{{ route('trips.create') }}" method="GET" class="flex flex-col md:flex-row gap-4">
                <div class="flex-grow text-left relative">
                    <label class="block text-xs font-bold text-journal-light uppercase tracking-wider mb-1">Where to?</label>
                    <div class="flex items-center border-b-2 border-journal-dark py-2">
                        <i class="fa-solid fa-location-dot text-journal-accent mr-3"></i>
                        <input type="text" name="destination" placeholder="E.g., Kyoto, Santorini, Banff..." class="appearance-none bg-transparent border-none w-full text-journal-dark mr-3 py-1 px-2 leading-tight focus:outline-none focus:ring-0 text-lg placeholder-gray-400">
                    </div>
                </div>
                
                <button type="submit" class="bg-journal-dark hover:bg-journal-accent text-white font-bold py-3 px-8 shadow-md transition-colors duration-300 flex items-center justify-center gap-2 self-end w-full md:w-auto h-12">
                    <i class="fa-solid fa-plane-departure"></i>
                    Plan My Trip
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Features Section -->
<div class="py-20 bg-journal-paper stamp-border m-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl font-serif font-bold text-journal-dark mb-16 relative inline-block">
            The WanderJournal Experience
            <span class="absolute -bottom-4 left-1/2 transform -translate-x-1/2 w-16 h-1 bg-journal-accent"></span>
        </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
            <div class="group">
                <div class="w-20 h-20 mx-auto bg-journal-bg rounded-full flex items-center justify-center mb-6 shadow-md border border-journal-border group-hover:bg-journal-accent transition-colors duration-300">
                    <i class="fa-solid fa-wand-magic-sparkles text-3xl text-journal-dark group-hover:text-white transition-colors duration-300"></i>
                </div>
                <h3 class="text-xl font-bold mb-3 font-serif">Smart Generation</h3>
                <p class="text-journal-light">Tell us your destination, budget, and travel style, and we'll craft a complete day-by-day itinerary in seconds.</p>
            </div>
            
            <div class="group">
                <div class="w-20 h-20 mx-auto bg-journal-bg rounded-full flex items-center justify-center mb-6 shadow-md border border-journal-border group-hover:bg-journal-olive transition-colors duration-300">
                    <i class="fa-solid fa-book-open text-3xl text-journal-dark group-hover:text-white transition-colors duration-300"></i>
                </div>
                <h3 class="text-xl font-bold mb-3 font-serif">Vintage Aesthetic</h3>
                <p class="text-journal-light">Plan your trip in an interface inspired by classic travel journals, complete with beautiful typography and paper textures.</p>
            </div>
            
            <div class="group">
                <div class="w-20 h-20 mx-auto bg-journal-bg rounded-full flex items-center justify-center mb-6 shadow-md border border-journal-border group-hover:bg-journal-gold transition-colors duration-300">
                    <i class="fa-solid fa-file-pdf text-3xl text-journal-dark group-hover:text-white transition-colors duration-300"></i>
                </div>
                <h3 class="text-xl font-bold mb-3 font-serif">Print & Go</h3>
                <p class="text-journal-light">Export your beautifully formatted itinerary as a PDF to print out and take with you on your grand adventure.</p>
            </div>
        </div>
    </div>
</div>

<!-- Featured Destinations -->
<div class="py-20 relative">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-end mb-12">
            <div>
                <span class="font-script text-2xl text-journal-accent block mb-1">Inspiration</span>
                <h2 class="text-4xl font-serif font-bold text-journal-dark">Trending Destinations</h2>
            </div>
            <a href="{{ route('destinations.index') }}" class="text-journal-olive font-medium hover:text-journal-dark flex items-center gap-2 border-b border-journal-olive pb-1">
                View All <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($featuredDestinations as $dest)
                <a href="{{ route('destinations.show', $dest) }}" class="group block relative h-96 bg-gray-200 overflow-hidden shadow-postcard transform transition hover:-translate-y-2 duration-300 border-8 border-white">
                    <img src="{{ $dest->cover_image_url }}" alt="{{ $dest->name }}" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                    
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
                    
                    <!-- Decorative tape -->
                    <div class="absolute top-0 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-24 h-6 bg-white/40 backdrop-blur-sm rotate-[-2deg] shadow-sm"></div>
                    
                    <div class="absolute bottom-0 left-0 right-0 p-6">
                        <div class="flex items-center gap-2 mb-2 text-journal-paper text-sm">
                            <i class="fa-solid fa-map-pin text-journal-accent"></i>
                            <span class="tracking-widest uppercase text-xs font-bold">{{ $dest->country }}</span>
                        </div>
                        <h3 class="text-3xl font-serif font-bold text-white mb-2">{{ $dest->name }}</h3>
                        <p class="text-gray-300 text-sm line-clamp-2">{{ $dest->short_description ?? $dest->description }}</p>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</div>

<!-- Stats -->
<div class="bg-journal-dark text-white py-16 relative overflow-hidden">
    <div class="absolute right-0 top-0 opacity-10">
        <i class="fa-solid fa-earth-americas text-[300px] -mr-20 -mt-20"></i>
    </div>
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center divide-y md:divide-y-0 md:divide-x divide-gray-700">
            <div class="py-4">
                <div class="text-4xl md:text-5xl font-serif font-bold text-journal-gold mb-2">{{ number_format($stats['users']) }}+</div>
                <div class="text-sm tracking-widest uppercase text-gray-400">Happy Travelers</div>
            </div>
            <div class="py-4">
                <div class="text-4xl md:text-5xl font-serif font-bold text-journal-gold mb-2">{{ number_format($stats['trips']) }}+</div>
                <div class="text-sm tracking-widest uppercase text-gray-400">Journeys Planned</div>
            </div>
            <div class="py-4">
                <div class="text-4xl md:text-5xl font-serif font-bold text-journal-gold mb-2">{{ $stats['destinations'] }}</div>
                <div class="text-sm tracking-widest uppercase text-gray-400">Curated Destinations</div>
            </div>
        </div>
    </div>
</div>
@endsection
