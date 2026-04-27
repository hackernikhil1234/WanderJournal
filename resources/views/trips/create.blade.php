@extends('layouts.app')

@section('title', 'Plan a Trip - WanderJournal')

@section('content')
<div class="py-12 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="bg-journal-paper p-8 md:p-12 shadow-postcard border border-journal-border relative">
        <div class="wax-seal">P</div>
        
        <div class="text-center mb-10">
            <h1 class="text-4xl font-serif font-bold text-journal-dark mb-2">Plan a New Journey</h1>
            <p class="text-journal-light">Fill out the details below and our smart engine will generate a complete itinerary.</p>
        </div>
        @php
            $destCurrencies = $destinations->mapWithKeys(function($dest) {
                return [$dest->id => $dest->currency];
            });
        @endphp
        <form action="{{ route('trips.store') }}" method="POST" class="space-y-8" 
              x-data="tripPlanner({{ $destCurrencies->toJson() }}, '{{ $destination ? $destination->id : '' }}')">
            @csrf
            
            <!-- Destination & Title -->
            <div class="bg-white p-6 border border-journal-border shadow-sm stamp-border">
                <h3 class="text-xl font-serif font-bold text-journal-dark mb-4 border-b border-journal-border pb-2">The Basics</h3>
                
                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label for="destination_id" class="block text-sm font-bold text-journal-dark uppercase tracking-wider mb-2">Destination *</label>
                        <select x-model="selectedDest" name="destination_id" id="destination_id" required class="w-full border-journal-border rounded-sm focus:ring-journal-accent focus:border-journal-accent bg-journal-bg">
                            <option value="">Select a destination...</option>
                            @foreach($destinations as $dest)
                                <option value="{{ $dest->id }}" {{ ($destination && $destination->id == $dest->id) ? 'selected' : '' }}>
                                    {{ $dest->name }}, {{ $dest->country }}
                                </option>
                            @endforeach
                        </select>
                        @error('destination_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    
                    <div>
                        <label for="title" class="block text-sm font-bold text-journal-dark uppercase tracking-wider mb-2">Trip Title *</label>
                        <input type="text" name="title" id="title" required value="{{ old('title') }}" placeholder="E.g., Summer Getaway 2024" class="w-full border-journal-border rounded-sm focus:ring-journal-accent focus:border-journal-accent bg-journal-bg">
                        @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    
                    <div>
                        <label for="dates" class="block text-sm font-bold text-journal-dark uppercase tracking-wider mb-2">Dates *</label>
                        <input type="text" name="dates" id="dates" required value="{{ old('dates') }}" placeholder="YYYY-MM-DD to YYYY-MM-DD" class="w-full border-journal-border rounded-sm focus:ring-journal-accent focus:border-journal-accent bg-journal-bg">
                        <p class="text-xs text-journal-light mt-1">E.g., 2024-06-15 to 2024-06-22</p>
                        @error('dates') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
            
            <!-- Travel Style -->
            <div class="bg-white p-6 border border-journal-border shadow-sm stamp-border">
                <h3 class="text-xl font-serif font-bold text-journal-dark mb-4 border-b border-journal-border pb-2">Travel Preferences</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="travel_style" class="block text-sm font-bold text-journal-dark uppercase tracking-wider mb-2">Travel Style *</label>
                        <select name="travel_style" id="travel_style" required class="w-full border-journal-border rounded-sm focus:ring-journal-accent focus:border-journal-accent bg-journal-bg">
                            <option value="budget">Budget-Friendly</option>
                            <option value="backpacker">Backpacker</option>
                            <option value="cultural">Cultural Immersion</option>
                            <option value="adventure">Adventure</option>
                            <option value="family">Family</option>
                            <option value="luxury">Luxury</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="budget" class="block text-sm font-bold text-journal-dark uppercase tracking-wider mb-2">Total Budget Estimate (Optional)</label>
                        <div class="flex items-center border border-journal-border rounded-sm bg-journal-bg focus-within:ring-1 focus-within:ring-journal-accent focus-within:border-journal-accent overflow-hidden">
                            <span class="pl-3 text-journal-dark font-bold" x-text="getCurrencySymbol()">$</span>
                            <input type="number" x-model="budget" name="budget" id="budget" min="0" step="50" class="w-full border-none focus:ring-0 bg-transparent" placeholder="Amount">
                            <span class="pr-3 text-journal-light text-xs font-bold" x-text="currentCurrencyCode()">USD</span>
                        </div>
                        
                        <!-- Currency Converter -->
                        <div class="mt-2 text-xs" x-show="currentCurrencyCode() !== userCurrency" x-cloak>
                            <button type="button" @click="showConverter = !showConverter" class="text-journal-olive hover:text-journal-dark font-bold underline">
                                <i class="fa-solid fa-calculator mr-1"></i>Convert to my currency
                            </button>
                            
                            <div x-show="showConverter" x-transition class="mt-2 p-3 bg-journal-paper border border-journal-border rounded-sm">
                                <p class="font-bold mb-2 text-journal-dark">Estimated Conversion</p>
                                <div class="flex items-center gap-2 mb-2">
                                    <select x-model="userCurrency" class="text-xs border-journal-border rounded-sm py-1 pl-2 pr-6">
                                        <option value="USD">USD ($)</option>
                                        <option value="EUR">EUR (€)</option>
                                        <option value="GBP">GBP (£)</option>
                                        <option value="INR">INR (₹)</option>
                                        <option value="AUD">AUD (A$)</option>
                                    </select>
                                    <span class="text-journal-light">=</span>
                                    <span class="font-bold text-journal-dark text-sm" x-text="convertedAmount()">0.00</span>
                                </div>
                                <p class="text-[10px] text-journal-light italic">* Using approximate mock exchange rates.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <label for="num_travelers" class="block text-sm font-bold text-journal-dark uppercase tracking-wider mb-2">Number of Travelers *</label>
                        <input type="number" name="num_travelers" id="num_travelers" required value="{{ old('num_travelers', 1) }}" min="1" max="20" class="w-full border-journal-border rounded-sm focus:ring-journal-accent focus:border-journal-accent bg-journal-bg">
                    </div>
                </div>
            </div>
            
            <!-- Interests -->
            <div class="bg-white p-6 border border-journal-border shadow-sm stamp-border">
                <h3 class="text-xl font-serif font-bold text-journal-dark mb-4 border-b border-journal-border pb-2">Interests</h3>
                <p class="text-sm text-journal-light mb-4">Select what you enjoy to help us tailor the itinerary.</p>
                
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    @php
                        $interests = [
                            'history' => 'Historical Sites',
                            'food' => 'Local Cuisine',
                            'nature' => 'Nature & Parks',
                            'art' => 'Art & Museums',
                            'shopping' => 'Shopping',
                            'nightlife' => 'Nightlife',
                            'relax' => 'Relaxation',
                            'active' => 'Active/Sports',
                        ];
                    @endphp
                    
                    @foreach($interests as $val => $label)
                    <label class="flex items-center space-x-3 cursor-pointer group">
                        <input type="checkbox" name="interests[]" value="{{ $val }}" class="rounded text-journal-accent focus:ring-journal-accent border-journal-border">
                        <span class="text-sm font-medium text-journal-dark group-hover:text-journal-accent transition-colors">{{ $label }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
            
            <div class="text-center pt-6 border-t-2 border-journal-border border-dashed">
                <button type="submit" class="bg-journal-dark hover:bg-journal-accent text-white font-serif font-bold py-4 px-12 text-xl shadow-md transition-all hover:scale-105 inline-flex items-center gap-3">
                    <i class="fa-solid fa-wand-magic-sparkles"></i> Generate Itinerary
                </button>
                <p class="text-xs text-journal-light mt-3 italic">This may take a moment while we carefully craft your journey.</p>
            </div>
        </form>
    </div>
</div>
@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('tripPlanner', (currencies, initialDest) => ({
            selectedDest: initialDest,
            budget: '{{ old('budget') }}',
            showConverter: false,
            userCurrency: 'USD',
            currencies: currencies,
            
            // Mock exchange rates relative to USD (1 USD = X)
            rates: {
                'USD': 1,
                'EUR': 0.92,
                'GBP': 0.79,
                'JPY': 150.5,
                'CAD': 1.36,
                'PEN': 3.75,
                'MAD': 10.05,
                'IDR': 15600,
                'INR': 83.3,
                'AUD': 1.52
            },
            
            symbols: {
                'USD': '$', 'EUR': '€', 'GBP': '£', 'JPY': '¥', 
                'CAD': 'C$', 'PEN': 'S/', 'MAD': 'MAD', 'IDR': 'Rp', 'INR': '₹', 'AUD': 'A$'
            },
            
            currentCurrencyCode() {
                if (!this.selectedDest || !this.currencies[this.selectedDest]) return 'USD';
                return this.currencies[this.selectedDest];
            },
            
            getCurrencySymbol() {
                const code = this.currentCurrencyCode();
                return this.symbols[code] || code;
            },
            
            convertedAmount() {
                if (!this.budget || isNaN(this.budget)) return '0.00';
                
                const destCode = this.currentCurrencyCode();
                const destRate = this.rates[destCode] || 1;
                const userRate = this.rates[this.userCurrency] || 1;
                
                // Convert Dest to USD, then USD to User
                const amountInUSD = parseFloat(this.budget) / destRate;
                const converted = amountInUSD * userRate;
                
                return new Intl.NumberFormat('en-US', { style: 'currency', currency: this.userCurrency }).format(converted);
            }
        }));
    });
</script>
@endpush
@endsection
