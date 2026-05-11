@extends('layouts.app')

@section('title', 'Dashboard - WanderJournal')

@push('styles')
<style>
.stat-card { transition: transform 0.2s, box-shadow 0.2s; }
.stat-card:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,0.08); }
.alert-pulse { animation: pulse 2s infinite; }
@keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.7} }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    {{-- Welcome Header --}}
    <div class="flex flex-col md:flex-row justify-between items-center mb-10 gap-6 bg-journal-dark p-8 text-white relative overflow-hidden">
        <i class="fa-solid fa-compass absolute -right-10 -bottom-10 text-[200px] opacity-5 text-journal-gold"></i>
        <div class="flex items-center gap-6 relative z-10">
            <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-20 h-20 rounded-full border-4 border-journal-paper shadow-sm">
            <div>
                <h1 class="text-3xl font-serif font-bold">Welcome back, {{ explode(' ', $user->name)[0] }}!</h1>
                <p class="text-gray-300 flex items-center gap-2 mt-1">
                    <i class="fa-solid fa-location-dot text-journal-accent"></i>
                    {{ $user->country ?? 'Global Explorer' }}
                </p>
                @if($stats['ai_trips'] > 0)
                <span class="mt-2 inline-flex items-center gap-1 text-xs bg-yellow-500/20 text-journal-gold border border-yellow-500/30 px-3 py-1 rounded-full">
                    <i class="fa-solid fa-wand-magic-sparkles"></i> {{ $stats['ai_trips'] }} AI-generated {{ Str::plural('trip', $stats['ai_trips']) }}
                </span>
                @endif
            </div>
        </div>
        <div class="relative z-10 flex gap-3">
            <a href="{{ route('trips.create') }}" class="bg-journal-gold hover:bg-white text-journal-dark font-bold py-3 px-6 shadow-sm transition inline-flex items-center gap-2 uppercase tracking-wider text-sm">
                <i class="fa-solid fa-plus"></i> New Journey
            </a>
            <a href="{{ route('community.feed') }}" class="border border-white/30 hover:bg-white/10 text-white font-bold py-3 px-6 transition inline-flex items-center gap-2 text-sm">
                <i class="fa-solid fa-earth-americas"></i> Explore
            </a>
        </div>
    </div>

    {{-- Trip Alerts --}}
    @if($tripAlerts->isNotEmpty())
    <div class="mb-8">
        @foreach($tripAlerts as $alertTrip)
        <div class="bg-amber-50 border border-amber-200 p-4 mb-2 flex items-center gap-3 alert-pulse">
            <i class="fa-solid fa-bell text-amber-500 text-xl"></i>
            <div>
                <span class="font-bold text-amber-800">{{ $alertTrip->title }}</span>
                <span class="text-amber-700"> starts in <strong>{{ $alertTrip->days_until_trip }} {{ Str::plural('day', $alertTrip->days_until_trip) }}</strong>!</span>
            </div>
            <a href="{{ route('trips.itinerary.show', $alertTrip) }}" class="ml-auto text-xs font-bold text-amber-700 hover:text-amber-900 border border-amber-300 px-3 py-1">View →</a>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Stats Grid --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-10">
        @php
        $statItems = [
            ['icon'=>'fa-plane','color'=>'text-journal-accent','bg'=>'bg-orange-50','value'=>$stats['total_trips'],'label'=>'Total Trips'],
            ['icon'=>'fa-earth-americas','color'=>'text-journal-olive','bg'=>'bg-green-50','value'=>$stats['countries_visited'],'label'=>'Countries'],
            ['icon'=>'fa-ticket','color'=>'text-journal-dark','bg'=>'bg-gray-50','value'=>$stats['upcoming_bookings'],'label'=>'Bookings'],
            ['icon'=>'fa-wallet','color'=>'text-journal-gold','bg'=>'bg-yellow-50','value'=>'$'.number_format($stats['total_spent']),'label'=>'Spent'],
            ['icon'=>'fa-sun','color'=>'text-blue-500','bg'=>'bg-blue-50','value'=>$stats['days_traveled'],'label'=>'Days Traveled'],
            ['icon'=>'fa-wand-magic-sparkles','color'=>'text-purple-500','bg'=>'bg-purple-50','value'=>$stats['ai_trips'],'label'=>'AI Trips'],
        ];
        @endphp
        @foreach($statItems as $s)
        <div class="bg-white border border-journal-border shadow-sm p-5 stat-card flex flex-col items-center text-center">
            <div class="w-10 h-10 {{ $s['bg'] }} rounded-full flex items-center justify-center mb-2">
                <i class="fa-solid {{ $s['icon'] }} {{ $s['color'] }}"></i>
            </div>
            <div class="text-2xl font-serif font-bold text-journal-dark">{{ $s['value'] }}</div>
            <div class="text-xs uppercase tracking-wider font-bold text-journal-light">{{ $s['label'] }}</div>
        </div>
        @endforeach
    </div>

    {{-- Charts Row --}}
    @if($monthlyExpenses->isNotEmpty() || $categoryBreakdown->isNotEmpty())
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10">
        <div class="bg-white border border-journal-border p-6 shadow-sm">
            <h3 class="font-serif font-bold text-journal-dark mb-4 flex items-center gap-2">
                <i class="fa-solid fa-chart-line text-journal-accent"></i> Monthly Spending
            </h3>
            <div id="monthlyChart" style="height:220px;"></div>
        </div>
        <div class="bg-white border border-journal-border p-6 shadow-sm">
            <h3 class="font-serif font-bold text-journal-dark mb-4 flex items-center gap-2">
                <i class="fa-solid fa-chart-pie text-journal-olive"></i> Spending by Category
            </h3>
            <div id="categoryChart" style="height:220px;"></div>
        </div>
    </div>
    @endif

    {{-- Upcoming Trips --}}
    <div class="mb-12">
        <div class="flex justify-between items-end mb-6 border-b-2 border-journal-border pb-2">
            <h2 class="text-2xl font-serif font-bold text-journal-dark">Upcoming Journeys</h2>
            <a href="{{ route('trips.index') }}" class="text-sm font-bold text-journal-olive hover:text-journal-dark uppercase tracking-wider">View All</a>
        </div>

        @if($upcomingTrips->isEmpty())
        <div class="bg-journal-paper p-10 text-center border border-dashed border-journal-border">
            <i class="fa-regular fa-calendar-xmark text-4xl text-journal-light mb-3"></i>
            <h3 class="text-xl font-serif text-journal-dark mb-1">No upcoming trips</h3>
            <p class="text-journal-light text-sm mb-4">Time to plan your next adventure!</p>
            <a href="{{ route('trips.create') }}" class="inline-flex items-center gap-2 bg-journal-dark text-white px-6 py-3 hover:bg-journal-accent transition text-sm font-bold uppercase tracking-wider">
                <i class="fa-solid fa-plus"></i> Plan a Trip
            </a>
        </div>
        @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($upcomingTrips as $trip)
            <div class="bg-white border border-journal-border shadow-postcard overflow-hidden group">
                <div class="h-36 overflow-hidden relative">
                    <img src="{{ $trip->destination->cover_image_url }}" alt="{{ $trip->destination->name }}"
                        class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent"></div>
                    @if($trip->ai_generated)
                    <div class="absolute top-2 left-2 bg-yellow-500/90 text-white text-xs font-bold px-2 py-1 flex items-center gap-1">
                        <i class="fa-solid fa-wand-magic-sparkles"></i> AI
                    </div>
                    @endif
                    @if($trip->days_until_trip <= 7)
                    <div class="absolute top-2 right-2 bg-red-500/90 text-white text-xs font-bold px-2 py-1 alert-pulse">
                        {{ $trip->days_until_trip }}d away!
                    </div>
                    @endif
                    <div class="absolute bottom-3 left-3 text-white">
                        <div class="text-xs uppercase tracking-wider font-bold opacity-80">{{ $trip->destination->country }}</div>
                        <h3 class="text-lg font-serif font-bold">{{ $trip->destination->name }}</h3>
                    </div>
                </div>
                <div class="p-5">
                    <h4 class="font-bold text-journal-dark mb-2 truncate" title="{{ $trip->title }}">{{ $trip->title }}</h4>
                    <div class="flex items-center gap-2 text-sm text-journal-light mb-3">
                        <i class="fa-regular fa-calendar"></i>
                        <span>{{ $trip->start_date->format('M d') }} - {{ $trip->end_date->format('M d, Y') }}</span>
                    </div>
                    {{-- Packing Progress --}}
                    <div class="mb-4">
                        <div class="flex justify-between text-xs font-bold uppercase tracking-wider mb-1">
                            <span class="text-journal-light">Packing</span>
                            <span class="text-journal-dark">{{ $trip->packing_progress }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 h-1.5">
                            <div class="bg-journal-olive h-1.5 transition-all duration-500" style="width:{{ $trip->packing_progress }}%"></div>
                        </div>
                    </div>
                    {{-- Budget Usage --}}
                    @if($trip->budget > 0)
                    <div class="mb-4">
                        <div class="flex justify-between text-xs font-bold uppercase tracking-wider mb-1">
                            <span class="text-journal-light">Budget Used</span>
                            <span class="{{ $trip->budget_used_percent > 80 ? 'text-red-500' : 'text-journal-dark' }}">{{ $trip->budget_used_percent }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 h-1.5">
                            <div class="h-1.5 transition-all duration-500 {{ $trip->budget_used_percent > 80 ? 'bg-red-500' : 'bg-journal-gold' }}"
                                style="width:{{ $trip->budget_used_percent }}%"></div>
                        </div>
                    </div>
                    @endif
                    <div class="grid grid-cols-3 gap-1">
                        <a href="{{ route('trips.itinerary.show', $trip) }}" class="text-center bg-journal-paper hover:bg-journal-dark hover:text-white border border-journal-border text-journal-dark py-2 text-xs font-bold uppercase tracking-wider transition">Plan</a>
                        <a href="{{ route('trips.packing.show', $trip) }}" class="text-center bg-journal-paper hover:bg-journal-dark hover:text-white border border-journal-border text-journal-dark py-2 text-xs font-bold uppercase tracking-wider transition">Pack</a>
                        <a href="{{ route('trips.expenses.index', $trip) }}" class="text-center bg-journal-paper hover:bg-journal-dark hover:text-white border border-journal-border text-journal-dark py-2 text-xs font-bold uppercase tracking-wider transition">$$</a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Past Trips --}}
    @if($pastTrips->isNotEmpty())
    <div class="mb-12">
        <div class="flex justify-between items-end mb-6 border-b-2 border-journal-border pb-2">
            <h2 class="text-2xl font-serif font-bold text-journal-dark">Travel Memories</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($pastTrips->take(8) as $trip)
            <a href="{{ route('trips.show', $trip) }}" class="group relative overflow-hidden bg-white border border-journal-border shadow-sm">
                <img src="{{ $trip->destination->cover_image_url }}" alt="{{ $trip->destination->name }}"
                    class="w-full h-36 object-cover group-hover:scale-105 transition duration-500 filter grayscale group-hover:grayscale-0">
                <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent"></div>
                <div class="absolute bottom-3 left-3 text-white">
                    <div class="text-xs text-gray-300">{{ $trip->start_date->format('M Y') }}</div>
                    <div class="font-serif font-bold">{{ $trip->destination->name }}</div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
@if($monthlyExpenses->isNotEmpty())
new ApexCharts(document.getElementById('monthlyChart'), {
    chart: { type: 'area', height: 220, toolbar: { show: false }, fontFamily: '"Lato", sans-serif' },
    series: [{ name: 'Spent', data: @json($monthlyExpenses->pluck('total')) }],
    xaxis: { categories: @json($monthlyExpenses->pluck('month')), labels: { style: { colors: '#8B857F', fontSize: '11px' } } },
    yaxis: { labels: { formatter: v => '$' + v.toLocaleString(), style: { colors: '#8B857F' } } },
    colors: ['#D35400'],
    fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05 } },
    stroke: { curve: 'smooth', width: 2 },
    dataLabels: { enabled: false },
    grid: { borderColor: '#E8E1D5' },
    tooltip: { theme: 'light' },
}).render();
@endif

@if($categoryBreakdown->isNotEmpty())
new ApexCharts(document.getElementById('categoryChart'), {
    chart: { type: 'donut', height: 220, fontFamily: '"Lato", sans-serif' },
    series: @json($categoryBreakdown->pluck('total')),
    labels: @json($categoryBreakdown->pluck('category')->map(fn($c) => ucfirst($c))),
    colors: ['#5A6E4D','#D35400','#2C2A29','#D4AF37','#8B4513','#c0392b','#2980b9'],
    legend: { position: 'bottom', fontSize: '11px' },
    dataLabels: { enabled: false },
    plotOptions: { pie: { donut: { size: '65%' } } },
    tooltip: { y: { formatter: v => '$' + v.toLocaleString() } },
}).render();
@endif
</script>
@endpush
