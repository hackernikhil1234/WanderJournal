@extends('layouts.app')

@section('title', 'Itinerary - ' . $trip->title)

@push('styles')
<style>
    .timeline-container {
        position: relative;
    }
    .timeline-container::before {
        content: '';
        position: absolute;
        top: 0;
        bottom: 0;
        left: 24px;
        width: 2px;
        background-color: #E8E1D5; /* journal-border */
        z-index: 0;
    }
    @media (min-width: 768px) {
        .timeline-container::before {
            left: 50%;
            transform: translateX(-50%);
        }
        .timeline-item:nth-child(odd) {
            padding-right: 50%;
        }
        .timeline-item:nth-child(even) {
            padding-left: 50%;
        }
        .timeline-item:nth-child(odd) .timeline-content {
            margin-right: 40px;
        }
        .timeline-item:nth-child(even) .timeline-content {
            margin-left: 40px;
        }
        .timeline-dot {
            left: 50%;
            transform: translateX(-50%);
        }
    }
    @media (max-width: 767px) {
        .timeline-item {
            padding-left: 60px;
        }
        .timeline-dot {
            left: 24px;
            transform: translateX(-50%);
        }
    }
    .sortable-ghost {
        opacity: 0.4;
        background-color: #f9fafb;
    }
    .sortable-drag {
        cursor: grabbing !important;
    }
    .leaflet-popup-content-wrapper {
        background-color: #FDFBF7;
        border: 1px solid #E8E1D5;
        border-radius: 2px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    .leaflet-popup-tip {
        background-color: #FDFBF7;
        border: 1px solid #E8E1D5;
    }
</style>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
@endpush

@section('content')
<!-- Header -->
<div class="bg-journal-dark text-white py-12 relative overflow-hidden">
    <!-- Map Background -->
    <div class="absolute inset-0 z-0 opacity-20 bg-cover bg-center" style="background-image: url('{{ $trip->destination->cover_image_url }}');"></div>
    <div class="absolute inset-0 bg-journal-dark/80 z-0"></div>
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 flex flex-col md:flex-row justify-between items-end gap-6">
        <div>
            <div class="flex items-center gap-2 mb-2 text-journal-gold text-sm font-bold tracking-wider uppercase">
                <a href="{{ route('trips.index') }}" class="hover:text-white transition"><i class="fa-solid fa-arrow-left mr-1"></i> Back</a>
                <span class="mx-2 text-gray-500">|</span>
                <span>{{ $trip->destination->name }}, {{ $trip->destination->country }}</span>
            </div>
            <h1 class="text-4xl md:text-5xl font-serif font-bold mb-2">{{ $trip->title }}</h1>
            <p class="text-gray-300 font-medium">
                {{ $trip->start_date->format('M d') }} - {{ $trip->end_date->format('M d, Y') }} 
                <span class="mx-2">&bull;</span> {{ $trip->num_days }} Days 
                <span class="mx-2">&bull;</span> {{ $trip->num_travelers }} Traveler(s)
            </p>
        </div>
        
        <div class="flex gap-3">
            <a href="{{ route('trips.pdf', $trip) }}" target="_blank" class="bg-white text-journal-dark hover:bg-journal-paper px-4 py-2 font-bold shadow-sm transition flex items-center gap-2 text-sm uppercase tracking-wider">
                <i class="fa-solid fa-file-pdf text-red-600"></i> Print PDF
            </a>
            <a href="{{ route('trips.packing.show', $trip) }}" class="bg-journal-olive text-white hover:bg-opacity-90 px-4 py-2 font-bold shadow-sm transition flex items-center gap-2 text-sm uppercase tracking-wider border border-journal-olive">
                <i class="fa-solid fa-suitcase"></i> Packing
            </a>
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="itineraryManager()">
    
    <!-- Day Navigation -->
    <div class="mb-10 overflow-x-auto pb-4">
        <div class="flex gap-2 min-w-max">
            <button @click="activeDay = 'all'" :class="{'bg-journal-dark text-white border-journal-dark': activeDay === 'all', 'bg-white text-journal-dark border-journal-border hover:bg-journal-paper': activeDay !== 'all'}" class="px-6 py-3 border font-serif font-bold transition shadow-sm whitespace-nowrap">
                Overview
            </button>
            @foreach($trip->itineraryDays as $day)
            <button @click="activeDay = {{ $day->id }}" :class="{'bg-journal-dark text-white border-journal-dark': activeDay === {{ $day->id }}, 'bg-white text-journal-dark border-journal-border hover:bg-journal-paper': activeDay !== {{ $day->id }}}" class="px-6 py-3 border font-serif font-bold transition shadow-sm whitespace-nowrap">
                Day {{ $day->day_number }} <span class="text-xs font-sans font-normal opacity-70 ml-1">{{ $day->date ? $day->date->format('D, M d') : '' }}</span>
            </button>
            @endforeach
        </div>
    </div>

    <!-- Overview Map (Leaflet) -->
    <div x-show="activeDay === 'all'" x-transition class="bg-white p-2 border border-journal-border shadow-postcard mb-12 relative stamp-border">
        <div class="absolute -top-3 -right-3 z-10 text-journal-dark transform rotate-[20deg] text-3xl opacity-80"><i class="fa-solid fa-paperclip"></i></div>
        <div id="itinerary-map" class="w-full h-[450px] bg-journal-paper relative z-0 filter sepia-[.3] contrast-125"></div>
    </div>

    <!-- Timeline Content -->
    <div>
        @foreach($trip->itineraryDays as $day)
        <div x-show="activeDay === 'all' || activeDay === {{ $day->id }}" x-transition.duration.300ms class="mb-16">
            
            <!-- Day Header -->
            <div class="flex items-center justify-between mb-8 pb-4 border-b-2 border-journal-dark">
                <div class="flex items-end gap-4">
                    <div class="text-5xl font-serif font-bold text-journal-accent leading-none">{{ sprintf('%02d', $day->day_number) }}</div>
                    <div>
                        <h2 class="text-2xl font-serif font-bold text-journal-dark">{{ $day->title ?? 'Day ' . $day->day_number }}</h2>
                        <div class="text-sm font-bold text-journal-light tracking-wider uppercase">{{ $day->date ? $day->date->format('l, F j, Y') : '' }}</div>
                    </div>
                </div>
                
                <button @click="openAddItemModal({{ $day->id }})" class="bg-journal-paper border border-journal-border text-journal-dark hover:text-journal-accent hover:border-journal-accent px-4 py-2 text-sm font-bold transition flex items-center gap-2">
                    <i class="fa-solid fa-plus"></i> Add Item
                </button>
            </div>

            <!-- Sortable List -->
            <div class="timeline-container sortable-list" data-day-id="{{ $day->id }}">
                @if($day->items->isEmpty())
                <div class="bg-white border border-dashed border-journal-border p-8 text-center text-journal-light italic mb-8">
                    Nothing planned for this day yet. Click "Add Item" to start building your itinerary.
                </div>
                @endif
                
                @foreach($day->items as $item)
                <div class="timeline-item relative mb-8 group cursor-grab active:cursor-grabbing" data-id="{{ $item->id }}">
                    <!-- Center Dot -->
                    <div class="timeline-dot absolute w-4 h-4 rounded-full border-4 border-white shadow-sm z-10 bg-{{ $item->type_badge_color }}-500"></div>
                    
                    <!-- Content Card -->
                    <div class="timeline-content bg-white p-5 border border-journal-border shadow-sm hover:shadow-md transition relative group-hover:border-{{ $item->type_badge_color }}-300">
                        <!-- Drag Handle -->
                        <div class="absolute top-2 left-2 text-gray-300 opacity-0 group-hover:opacity-100 transition"><i class="fa-solid fa-grip-vertical"></i></div>
                        
                        <!-- Delete Button -->
                        <form action="{{ route('trips.itinerary.items.destroy', [$trip, $item]) }}" method="POST" class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition" onsubmit="return confirm('Are you sure you want to remove this item?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-400 hover:text-red-600"><i class="fa-solid fa-trash-can"></i></button>
                        </form>

                        <div class="flex flex-col sm:flex-row gap-4 ml-4">
                            <!-- Time Column -->
                            <div class="sm:w-24 flex-shrink-0 pt-1">
                                @if($item->start_time)
                                <div class="text-lg font-bold font-serif text-journal-dark">{{ \Carbon\Carbon::parse($item->start_time)->format('h:i A') }}</div>
                                @if($item->end_time)
                                <div class="text-xs text-journal-light tracking-wide">{{ \Carbon\Carbon::parse($item->end_time)->format('h:i A') }}</div>
                                @endif
                                @else
                                <div class="text-sm font-bold text-journal-light uppercase tracking-wider">Anytime</div>
                                @endif
                            </div>
                            
                            <!-- Detail Column -->
                            <div class="flex-grow">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="w-8 h-8 rounded-full bg-{{ $item->type_badge_color }}-100 text-{{ $item->type_badge_color }}-700 flex items-center justify-center text-sm border border-{{ $item->type_badge_color }}-200">
                                        {{ $item->type_icon }}
                                    </span>
                                    <span class="text-xs font-bold uppercase tracking-wider text-{{ $item->type_badge_color }}-700">{{ $item->type }}</span>
                                    
                                    @if($item->cost > 0)
                                    <span class="ml-auto text-xs font-bold bg-green-100 text-green-800 px-2 py-1 rounded-sm border border-green-200">
                                        {{ $trip->currency }} {{ number_format($item->cost, 2) }}
                                    </span>
                                    @endif
                                </div>
                                
                                <h3 class="text-xl font-serif font-bold text-journal-dark mb-2">{{ $item->title }}</h3>
                                
                                @if($item->location)
                                <p class="text-sm text-journal-olive mb-3 flex items-start gap-1">
                                    <i class="fa-solid fa-location-dot mt-1 flex-shrink-0"></i> 
                                    <span>{{ $item->location }}</span>
                                </p>
                                @endif
                                
                                @if($item->description)
                                <p class="text-journal-dark font-light text-sm leading-relaxed">{{ $item->description }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
    
    <!-- Add Item Modal -->
    <div x-show="isModalOpen" x-transition.opacity class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            
            <div class="fixed inset-0 bg-journal-dark bg-opacity-75 transition-opacity" aria-hidden="true" @click="closeModal()"></div>
            
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            
            <div class="inline-block align-bottom bg-journal-paper border border-journal-border text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full relative">
                <!-- Modal content -->
                <div class="absolute top-0 right-0 pt-4 pr-4">
                    <button @click="closeModal()" type="button" class="text-journal-light hover:text-journal-dark">
                        <i class="fa-solid fa-times text-xl"></i>
                    </button>
                </div>
                
                <form :action="'/trips/{{ $trip->id }}/itinerary/days/' + selectedDayId + '/items'" method="POST" class="p-6 sm:p-8">
                    @csrf
                    <div class="text-center mb-6">
                        <h3 class="text-2xl font-serif font-bold text-journal-dark" id="modal-title">Add Itinerary Item</h3>
                        <p class="text-journal-light text-sm mt-1">Add a new activity, meal, or transport.</p>
                    </div>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-journal-dark uppercase tracking-wider mb-1">Title *</label>
                            <input type="text" name="title" required class="w-full border-journal-border bg-white focus:ring-journal-accent focus:border-journal-accent rounded-sm shadow-sm text-sm">
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-journal-dark uppercase tracking-wider mb-1">Type *</label>
                                <select name="type" required class="w-full border-journal-border bg-white focus:ring-journal-accent focus:border-journal-accent rounded-sm shadow-sm text-sm">
                                    <option value="attraction">🏛️ Attraction</option>
                                    <option value="restaurant">🍽️ Restaurant</option>
                                    <option value="hotel">🏨 Hotel</option>
                                    <option value="transport">🚌 Transport</option>
                                    <option value="activity">🎯 Activity</option>
                                    <option value="shopping">🛍️ Shopping</option>
                                    <option value="other">📍 Other</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-journal-dark uppercase tracking-wider mb-1">Cost ({{ $trip->currency }})</label>
                                <input type="number" name="cost" step="0.01" min="0" class="w-full border-journal-border bg-white focus:ring-journal-accent focus:border-journal-accent rounded-sm shadow-sm text-sm">
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-journal-dark uppercase tracking-wider mb-1">Start Time</label>
                                <input type="time" name="start_time" class="w-full border-journal-border bg-white focus:ring-journal-accent focus:border-journal-accent rounded-sm shadow-sm text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-journal-dark uppercase tracking-wider mb-1">End Time</label>
                                <input type="time" name="end_time" class="w-full border-journal-border bg-white focus:ring-journal-accent focus:border-journal-accent rounded-sm shadow-sm text-sm">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-bold text-journal-dark uppercase tracking-wider mb-1">Location / Address</label>
                            <input type="text" name="location" class="w-full border-journal-border bg-white focus:ring-journal-accent focus:border-journal-accent rounded-sm shadow-sm text-sm">
                        </div>
                        
                        <div>
                            <label class="block text-xs font-bold text-journal-dark uppercase tracking-wider mb-1">Notes / Description</label>
                            <textarea name="description" rows="3" class="w-full border-journal-border bg-white focus:ring-journal-accent focus:border-journal-accent rounded-sm shadow-sm text-sm"></textarea>
                        </div>
                    </div>
                    
                    <div class="mt-8">
                        <button type="submit" class="w-full bg-journal-dark hover:bg-journal-accent text-white font-bold py-3 uppercase tracking-wider shadow-sm transition-colors text-sm">
                            Save Item
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('itineraryManager', () => ({
            activeDay: 'all',
            isModalOpen: false,
            selectedDayId: null,
            
            openAddItemModal(dayId) {
                this.selectedDayId = dayId;
                this.isModalOpen = true;
                // document.body.style.overflow = 'hidden';
            },
            
            closeModal() {
                this.isModalOpen = false;
                // document.body.style.overflow = '';
            },
            
            init() {
                // Initialize Sortable.js for drag and drop reordering
                const lists = document.querySelectorAll('.sortable-list');
                
                lists.forEach(el => {
                    new Sortable(el, {
                        group: 'shared', // set both lists to same group
                        animation: 150,
                        handle: '.timeline-item',
                        ghostClass: 'sortable-ghost',
                        dragClass: 'sortable-drag',
                        onEnd: (evt) => {
                            this.saveOrder();
                        }
                    });
                });
                
                // Initialize Leaflet Map
                this.initMap();
            },
            
            initMap() {
                // Wait for Alpine to render before initializing map to avoid size issues
                setTimeout(() => {
                    const map = L.map('itinerary-map').setView([{{ $trip->destination->latitude }}, {{ $trip->destination->longitude }}], 12);
                    
                    // CartoDB Voyager tiles (clean, light, works well with our sepia filter)
                    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
                        subdomains: 'abcd',
                        maxZoom: 20
                    }).addTo(map);
                    
                    // Custom vintage pin icon
                    const pinIcon = L.divIcon({
                        html: '<i class="fa-solid fa-map-pin text-4xl text-journal-accent drop-shadow-md" style="transform: rotate(-15deg);"></i>',
                        className: 'custom-div-icon bg-transparent border-0',
                        iconSize: [30, 42],
                        iconAnchor: [15, 42],
                        popupAnchor: [0, -35]
                    });
                    
                    L.marker([{{ $trip->destination->latitude }}, {{ $trip->destination->longitude }}], {icon: pinIcon})
                     .addTo(map)
                     .bindPopup('<div class="font-serif font-bold text-lg text-journal-dark px-2 text-center">{{ $trip->destination->name }}</div>')
                     .openPopup();
                     
                    // Fix map rendering issues when unhidden by Alpine tabs
                    this.$watch('activeDay', value => {
                        if(value === 'all') {
                            setTimeout(() => map.invalidateSize(), 100);
                        }
                    });
                }, 100);
            },
            
            saveOrder() {
                const lists = document.querySelectorAll('.sortable-list');
                let orderData = [];
                
                lists.forEach(list => {
                    const dayId = list.dataset.dayId;
                    const items = list.querySelectorAll('.timeline-item');
                    
                    items.forEach((item, index) => {
                        orderData.push({
                            id: item.dataset.id,
                            day_id: dayId,
                            order: index + 1
                        });
                    });
                });
                
                // Send AJAX request
                fetch(`{{ route('trips.itinerary.reorder', $trip) }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ items: orderData })
                })
                .then(response => response.json())
                .then(data => {
                    console.log('Order saved!', data);
                })
                .catch((error) => {
                    console.error('Error:', error);
                });
            }
        }));
    });
</script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
@endpush
@endsection
