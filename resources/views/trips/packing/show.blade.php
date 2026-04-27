@extends('layouts.app')

@section('title', 'Packing List - ' . $trip->title)

@section('content')
<div class="bg-journal-dark text-white py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-end gap-6">
        <div>
            <div class="flex items-center gap-2 mb-2 text-journal-gold text-sm font-bold tracking-wider uppercase">
                <a href="{{ route('trips.itinerary.show', $trip) }}" class="hover:text-white transition"><i class="fa-solid fa-arrow-left mr-1"></i> Back to Itinerary</a>
            </div>
            <h1 class="text-4xl font-serif font-bold mb-2">Packing List</h1>
            <p class="text-gray-300">Prepare for {{ $trip->title }}</p>
        </div>
        
        <div class="bg-journal-paper/10 px-6 py-4 rounded-sm border border-journal-paper/20 text-center min-w-[200px]" x-data="{ progress: {{ $trip->packing_progress }} }" @update-progress.window="progress = $event.detail.progress">
            <div class="text-sm uppercase tracking-wider text-gray-300 font-bold mb-2">Packing Progress</div>
            <div class="flex items-center gap-4">
                <div class="flex-grow bg-journal-dark/50 rounded-full h-3 border border-journal-paper/20 overflow-hidden">
                    <div class="bg-journal-accent h-full transition-all duration-500 ease-out" :style="'width: ' + progress + '%'"></div>
                </div>
                <div class="font-serif font-bold text-xl text-journal-gold" x-text="progress + '%'"></div>
            </div>
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12" x-data="packingManager()">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
        
        <!-- Checklist -->
        <div class="lg:col-span-2">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                @foreach($items as $category => $categoryItems)
                <div class="bg-journal-paper border border-journal-border shadow-sm p-6 relative">
                    <div class="absolute -top-3 -right-3 text-journal-light opacity-50 text-2xl transform rotate-12"><i class="fa-solid fa-thumbtack"></i></div>
                    
                    <h3 class="text-xl font-serif font-bold text-journal-dark mb-4 border-b border-journal-border pb-2 flex items-center gap-2">
                        <span>{{ $categoryItems->first()->category_icon }}</span> 
                        <span class="capitalize">{{ $category }}</span>
                    </h3>
                    
                    <ul class="space-y-3">
                        @foreach($categoryItems as $item)
                        <li class="flex items-start justify-between group" id="item-{{ $item->id }}">
                            <label class="flex items-start gap-3 cursor-pointer flex-grow">
                                <input type="checkbox" 
                                       class="mt-1 w-5 h-5 rounded-sm border-journal-border text-journal-olive focus:ring-journal-olive cursor-pointer" 
                                       {{ $item->is_packed ? 'checked' : '' }}
                                       @change="toggleItem({{ $item->id }})">
                                <div class="flex flex-col">
                                    <span class="text-journal-dark font-medium {{ $item->is_packed ? 'line-through opacity-50' : '' }}" id="text-{{ $item->id }}">
                                        {{ $item->name }} 
                                        @if($item->quantity > 1) <span class="text-xs bg-journal-dark text-white px-1.5 py-0.5 rounded-sm ml-1">{{ $item->quantity }}</span> @endif
                                    </span>
                                    @if($item->notes)
                                    <span class="text-xs text-journal-light {{ $item->is_packed ? 'opacity-50' : '' }}">{{ $item->notes }}</span>
                                    @endif
                                </div>
                            </label>
                            
                            <form action="{{ route('trips.packing.destroy', [$trip, $item]) }}" method="POST" class="opacity-0 group-hover:opacity-100 transition-opacity" onsubmit="return confirm('Remove this item?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-400 hover:text-red-600 p-1"><i class="fa-solid fa-times"></i></button>
                            </form>
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endforeach
            </div>
            
            @if($items->isEmpty())
            <div class="bg-white border border-dashed border-journal-border p-12 text-center">
                <i class="fa-solid fa-suitcase-rolling text-4xl text-journal-light mb-4"></i>
                <h3 class="text-xl font-serif text-journal-dark mb-2">Your packing list is empty</h3>
                <p class="text-journal-light">Add items using the form to start preparing for your trip.</p>
            </div>
            @endif
        </div>
        
        <!-- Add Item Form -->
        <div class="lg:col-span-1">
            <div class="bg-white border border-journal-border shadow-postcard p-6 sticky top-8">
                <h3 class="text-xl font-serif font-bold text-journal-dark mb-4 border-b border-journal-border pb-2">Add New Item</h3>
                
                <form action="{{ route('trips.packing.store', $trip) }}" method="POST" class="space-y-4">
                    @csrf
                    
                    <div>
                        <label class="block text-xs font-bold text-journal-dark uppercase tracking-wider mb-1">Item Name *</label>
                        <input type="text" name="name" required class="w-full border-journal-border focus:ring-journal-accent focus:border-journal-accent rounded-sm shadow-sm text-sm" placeholder="E.g., Rain jacket">
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-journal-dark uppercase tracking-wider mb-1">Category *</label>
                            <select name="category" required class="w-full border-journal-border focus:ring-journal-accent focus:border-journal-accent rounded-sm shadow-sm text-sm">
                                <option value="clothing">👕 Clothing</option>
                                <option value="essentials">🎒 Essentials</option>
                                <option value="electronics">💻 Electronics</option>
                                <option value="toiletries">🪥 Toiletries</option>
                                <option value="documents">📄 Documents</option>
                                <option value="health">💊 Health</option>
                                <option value="entertainment">🎮 Entertainment</option>
                                <option value="other">📦 Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-journal-dark uppercase tracking-wider mb-1">Quantity</label>
                            <input type="number" name="quantity" value="1" min="1" required class="w-full border-journal-border focus:ring-journal-accent focus:border-journal-accent rounded-sm shadow-sm text-sm">
                        </div>
                    </div>
                    
                    <div>
                        <button type="submit" class="w-full bg-journal-dark hover:bg-journal-accent text-white font-bold py-2 px-4 shadow-sm transition uppercase tracking-wider text-sm mt-4">
                            Add to List
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
        Alpine.data('packingManager', () => ({
            toggleItem(itemId) {
                // Optimistic UI update
                const textEl = document.getElementById(`text-${itemId}`);
                if (textEl.classList.contains('line-through')) {
                    textEl.classList.remove('line-through', 'opacity-50');
                } else {
                    textEl.classList.add('line-through', 'opacity-50');
                }
                
                // AJAX request
                fetch(`/trips/{{ $trip->id }}/packing/${itemId}/toggle`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        // Dispatch event to update progress bar
                        window.dispatchEvent(new CustomEvent('update-progress', {
                            detail: { progress: data.progress }
                        }));
                    }
                })
                .catch(error => {
                    console.error('Error toggling item:', error);
                    // Revert UI if error
                    alert('Error updating item status.');
                });
            }
        }));
    });
</script>
@endpush
@endsection
