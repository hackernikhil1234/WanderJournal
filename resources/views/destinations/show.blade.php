@extends('layouts.app')

@section('title', $destination->name . ' - WanderJournal')

@section('content')
<!-- Hero Image -->
<div class="relative h-[60vh] w-full bg-gray-900">
    <img src="{{ $destination->cover_image_url }}" alt="{{ $destination->name }}" class="w-full h-full object-cover opacity-60">
    
    <div class="absolute inset-0 bg-gradient-to-t from-journal-dark via-transparent to-transparent"></div>
    
    <div class="absolute bottom-0 left-0 right-0 p-8 max-w-7xl mx-auto">
        <div class="flex items-center gap-2 mb-2 text-journal-gold">
            <i class="fa-solid fa-map-pin"></i>
            <span class="tracking-widest uppercase font-bold">{{ $destination->country }}, {{ $destination->continent }}</span>
        </div>
        <h1 class="text-6xl md:text-8xl font-serif font-bold text-white mb-4 drop-shadow-md">{{ $destination->name }}</h1>
        <div class="flex gap-4 items-center">
            <span class="bg-journal-accent text-white px-3 py-1 text-sm uppercase tracking-wider font-bold">
                {{ ucfirst($destination->category) }}
            </span>
            <span class="text-white flex items-center gap-1">
                <i class="fa-solid fa-star text-journal-gold"></i> {{ number_format($destination->average_rating, 1) }} ({{ $destination->reviews_count }} reviews)
            </span>
        </div>
    </div>
</div>

<!-- Content Grid -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
        
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-12">
            
            <!-- Description Card -->
            <div class="bg-journal-paper p-8 shadow-postcard border border-journal-border relative">
                <div class="wax-seal">W</div>
                <h2 class="text-3xl font-serif font-bold text-journal-dark mb-6">About {{ $destination->name }}</h2>
                <div class="prose prose-lg prose-stone text-journal-dark font-light leading-relaxed">
                    <p>{{ $destination->description }}</p>
                </div>
            </div>
            
            <!-- Highlights -->
            @if($destination->highlights)
            <div>
                <h2 class="text-3xl font-serif font-bold text-journal-dark mb-6 border-b-2 border-journal-border pb-2">Top Experiences</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    @foreach($destination->highlights as $highlight)
                    <div class="bg-white p-6 border border-journal-border shadow-sm flex items-start gap-4 hover:shadow-md transition">
                        <div class="bg-journal-bg text-journal-accent rounded-full w-10 h-10 flex items-center justify-center flex-shrink-0 border border-journal-border">
                            <i class="fa-solid fa-camera"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg font-serif mb-1">{{ $highlight['name'] }}</h3>
                            <p class="text-journal-light text-sm">{{ $highlight['description'] }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Weather Widget (Vue/Alpine via API) -->
            <div x-data="weatherWidget('{{ $destination->latitude }}', '{{ $destination->longitude }}')" class="bg-gradient-to-br from-blue-50 to-blue-100 p-8 border border-blue-200 rounded-sm shadow-sm relative overflow-hidden">
                <i class="fa-solid fa-cloud-sun text-9xl absolute -right-10 -top-10 text-blue-200 opacity-50"></i>
                <h2 class="text-2xl font-serif font-bold text-blue-900 mb-6 relative z-10">5-Day Weather Forecast</h2>
                
                <template x-if="loading">
                    <div class="flex justify-center py-8">
                        <i class="fa-solid fa-circle-notch fa-spin text-3xl text-blue-500"></i>
                    </div>
                </template>
                
                <template x-if="error">
                    <div class="text-red-500 bg-red-50 p-4 rounded-sm border border-red-200">
                        <p x-text="error"></p>
                    </div>
                </template>
                
                <template x-if="weather && !loading && !error">
                    <div class="grid grid-cols-5 gap-4 relative z-10">
                        <template x-for="day in weather.daily" :key="day.date">
                            <div class="bg-white/80 backdrop-blur p-3 text-center border border-white shadow-sm rounded-sm">
                                <div class="text-xs font-bold text-blue-900 uppercase tracking-wider mb-2" x-text="formatDate(day.date)"></div>
                                <img :src="day.icon_url" :alt="day.description" class="w-12 h-12 mx-auto filter drop-shadow-sm">
                                <div class="text-2xl font-bold text-blue-900" x-html="day.temp + '&deg;'"></div>
                                <div class="text-xs text-blue-700 capitalize mt-1" x-text="day.condition"></div>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
            
        </div>
        
        <!-- Sidebar -->
        <div class="lg:col-span-1 space-y-8">
            
            <!-- Plan Trip CTA -->
            <div class="bg-journal-dark text-white p-8 shadow-postcard text-center">
                <i class="fa-solid fa-ticket text-4xl text-journal-gold mb-4"></i>
                <h3 class="text-2xl font-serif font-bold mb-4">Ready to go?</h3>
                <p class="text-gray-300 text-sm mb-6">Let WanderJournal generate a complete day-by-day itinerary tailored to your style and budget.</p>
                <a href="{{ route('trips.create', ['destination' => $destination->slug]) }}" class="block w-full bg-journal-accent hover:bg-journal-gold hover:text-journal-dark transition-colors text-white font-bold py-3 px-4 uppercase tracking-wider text-sm shadow-sm">
                    Plan Trip Here
                </a>
            </div>
            
            <!-- Quick Facts -->
            <div class="bg-white p-6 border border-journal-border shadow-sm stamp-border">
                <h3 class="text-xl font-serif font-bold text-journal-dark mb-4 border-b border-journal-border pb-2">Travel Notes</h3>
                <ul class="space-y-4">
                    <li class="flex items-start gap-3">
                        <i class="fa-regular fa-calendar text-journal-olive mt-1"></i>
                        <div>
                            <span class="block text-xs font-bold uppercase tracking-wider text-journal-light">Best Time to Visit</span>
                            <span class="text-journal-dark font-medium">{{ $destination->best_time_to_visit ?? 'Year-round' }}</span>
                        </div>
                    </li>
                    <li class="flex items-start gap-3">
                        <i class="fa-solid fa-wallet text-journal-olive mt-1"></i>
                        <div>
                            <span class="block text-xs font-bold uppercase tracking-wider text-journal-light">Avg. Daily Budget</span>
                            <span class="text-journal-dark font-medium">${{ $destination->avg_daily_budget }} {{ $destination->currency }}</span>
                        </div>
                    </li>
                    @if($destination->language)
                    <li class="flex items-start gap-3">
                        <i class="fa-solid fa-language text-journal-olive mt-1"></i>
                        <div>
                            <span class="block text-xs font-bold uppercase tracking-wider text-journal-light">Language</span>
                            <span class="text-journal-dark font-medium">{{ $destination->language }}</span>
                        </div>
                    </li>
                    @endif
                </ul>
            </div>
            
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('weatherWidget', (lat, lon) => ({
            weather: null,
            loading: true,
            error: null,
            
            init() {
                if(!lat || !lon) {
                    this.error = "Location data missing.";
                    this.loading = false;
                    return;
                }
                
                fetch(`/api/weather?lat=${lat}&lon=${lon}`)
                    .then(res => {
                        if (!res.ok) throw new Error('Network response was not ok');
                        return res.json();
                    })
                    .then(data => {
                        this.weather = data;
                        this.loading = false;
                    })
                    .catch(err => {
                        console.error(err);
                        this.error = "Could not load weather data.";
                        this.loading = false;
                    });
            },
            
            formatDate(dateStr) {
                const date = new Date(dateStr);
                return date.toLocaleDateString('en-US', { weekday: 'short' });
            }
        }))
    })
</script>
@endpush
@endsection
