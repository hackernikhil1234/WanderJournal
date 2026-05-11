@extends('layouts.app')

@section('title', 'Plan a New Journey - WanderJournal')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
.step-indicator { display: flex; align-items: center; gap: 0; }
.step { display: flex; flex-direction: column; align-items: center; position: relative; flex: 1; }
.step:not(:last-child)::after { content: ''; position: absolute; top: 20px; left: 50%; width: 100%; height: 2px; background: #E8E1D5; z-index: 0; }
.step.active:not(:last-child)::after, .step.done:not(:last-child)::after { background: #D4AF37; }
.step-circle { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 14px; position: relative; z-index: 1; transition: all 0.3s; }
.step.pending .step-circle { background: #F5F0E6; border: 2px solid #E8E1D5; color: #8B857F; }
.step.active .step-circle { background: #D35400; border: 2px solid #D35400; color: white; }
.step.done .step-circle { background: #5A6E4D; border: 2px solid #5A6E4D; color: white; }
.interest-chip { cursor: pointer; transition: all 0.2s; }
.interest-chip input:checked + label { background: #D35400; color: white; border-color: #D35400; }
.interest-chip label { cursor: pointer; display: block; padding: 6px 14px; border: 1.5px solid #E8E1D5; border-radius: 999px; font-size: 0.8rem; font-weight: 600; color: #8B857F; transition: all 0.2s; }
.interest-chip label:hover { border-color: #D35400; color: #D35400; }
.style-card input:checked + label { border-color: #D35400; background: #FFF8F5; }
.style-card label { cursor: pointer; display: block; }
</style>
@endpush

@section('content')
<div class="max-w-4xl mx-auto px-4 py-12" x-data="tripWizard()">

    {{-- Header --}}
    <div class="text-center mb-10">
        <span class="font-script text-3xl text-journal-accent block mb-2">Begin your adventure</span>
        <h1 class="text-4xl font-serif font-bold text-journal-dark">Plan Your Perfect Journey</h1>
        @if($aiAvailable)
        <p class="text-journal-light mt-3 flex items-center justify-center gap-2">
            <i class="fa-solid fa-wand-magic-sparkles text-journal-gold"></i>
            AI-powered planning is active — get a personalized itinerary instantly
        </p>
        @endif
    </div>

    {{-- Step Indicator --}}
    <div class="step-indicator mb-10 px-8">
        <div class="step" :class="step >= 1 ? (step > 1 ? 'done' : 'active') : 'pending'">
            <div class="step-circle"><i x-show="step > 1" class="fa-solid fa-check text-xs"></i><span x-show="step <= 1">1</span></div>
            <span class="text-xs mt-2 font-bold uppercase tracking-wider text-journal-light">Destination</span>
        </div>
        <div class="step" :class="step >= 2 ? (step > 2 ? 'done' : 'active') : 'pending'">
            <div class="step-circle"><i x-show="step > 2" class="fa-solid fa-check text-xs"></i><span x-show="step <= 2">2</span></div>
            <span class="text-xs mt-2 font-bold uppercase tracking-wider text-journal-light">Details</span>
        </div>
        <div class="step" :class="step >= 3 ? (step > 3 ? 'done' : 'active') : 'pending'">
            <div class="step-circle"><i x-show="step > 3" class="fa-solid fa-check text-xs"></i><span x-show="step <= 3">3</span></div>
            <span class="text-xs mt-2 font-bold uppercase tracking-wider text-journal-light">Preferences</span>
        </div>
        <div class="step" :class="step >= 4 ? 'active' : 'pending'">
            <div class="step-circle">4</div>
            <span class="text-xs mt-2 font-bold uppercase tracking-wider text-journal-light">Budget & AI</span>
        </div>
    </div>

    <form method="POST" action="{{ route('trips.store') }}" id="tripForm">
        @csrf

        {{-- =================== STEP 1: Destination & Dates =================== --}}
        <div x-show="step === 1" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">
            <div class="bg-white border border-journal-border shadow-postcard p-8">
                <h2 class="text-2xl font-serif font-bold text-journal-dark mb-6 flex items-center gap-3">
                    <i class="fa-solid fa-map-location-dot text-journal-accent"></i> Where are you going?
                </h2>

                <div class="mb-6">
                    <label class="block text-xs font-bold uppercase tracking-wider text-journal-light mb-2">Trip Title *</label>
                    <input type="text" name="title" x-model="form.title" placeholder="e.g. Summer in Kyoto, Backpacking Southeast Asia..."
                        class="w-full border-b-2 border-journal-border bg-transparent py-3 text-journal-dark placeholder-gray-400 focus:outline-none focus:border-journal-accent text-lg font-serif" required>
                </div>

                <div class="mb-6">
                    <label class="block text-xs font-bold uppercase tracking-wider text-journal-light mb-2">Destination *</label>
                    <div class="relative">
                        <i class="fa-solid fa-location-dot absolute left-3 top-4 text-journal-accent"></i>
                        <select name="destination_id" x-model="form.destination_id"
                            class="w-full pl-10 pr-4 py-3 border border-journal-border bg-white text-journal-dark focus:outline-none focus:border-journal-accent appearance-none" required>
                            <option value="">Select a destination...</option>
                            @foreach($destinations as $dest)
                            <option value="{{ $dest->id }}" {{ old('destination_id', $destination?->id) == $dest->id ? 'selected' : '' }}>
                                {{ $dest->name }}, {{ $dest->country }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-xs font-bold uppercase tracking-wider text-journal-light mb-2">Travel Dates *</label>
                    <div class="flex items-center border border-journal-border bg-white" @click="document.getElementById('datepicker').dispatchEvent(new MouseEvent('click'))">
                        <i class="fa-regular fa-calendar px-3 text-journal-accent"></i>
                        <input type="text" id="datepicker"
                            placeholder="Select your travel dates..." readonly
                            class="flex-1 py-3 px-2 bg-transparent text-journal-dark focus:outline-none cursor-pointer">
                        {{-- hidden input carries the value for form submission & Alpine --}}
                        <input type="hidden" name="dates" x-bind:value="form.dates">
                    </div>
                    <p class="text-xs text-journal-light mt-1">Select start and end date. Single day trips are allowed.</p>
                </div>
            </div>

            <div class="flex justify-end mt-6">
                <button type="button" @click="nextStep()" :disabled="!form.destination_id || !form.title || !form.dates"
                    class="bg-journal-dark hover:bg-journal-accent text-white font-bold py-3 px-8 transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                    Continue <i class="fa-solid fa-arrow-right"></i>
                </button>
            </div>
        </div>

        {{-- =================== STEP 2: Travelers & Style =================== --}}
        <div x-show="step === 2" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">
            <div class="bg-white border border-journal-border shadow-postcard p-8">
                <h2 class="text-2xl font-serif font-bold text-journal-dark mb-6 flex items-center gap-3">
                    <i class="fa-solid fa-users text-journal-accent"></i> Who's traveling & how?
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-journal-light mb-2">Number of Travelers *</label>
                        <div class="flex items-center border border-journal-border">
                            <button type="button" @click="form.num_travelers = Math.max(1, form.num_travelers - 1)"
                                class="px-4 py-3 text-journal-accent hover:bg-journal-paper transition"><i class="fa-solid fa-minus"></i></button>
                            <span x-text="form.num_travelers" class="flex-1 text-center font-bold text-xl font-serif"></span>
                            <input type="hidden" name="num_travelers" :value="form.num_travelers">
                            <button type="button" @click="form.num_travelers = Math.min(50, form.num_travelers + 1)"
                                class="px-4 py-3 text-journal-accent hover:bg-journal-paper transition"><i class="fa-solid fa-plus"></i></button>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-journal-light mb-2">Budget (Optional)</label>
                        <div class="flex">
                            <select name="currency" x-model="form.currency" class="border border-r-0 border-journal-border bg-journal-paper px-3 py-3 text-sm focus:outline-none">
                                <option value="USD">$ USD</option>
                                <option value="EUR">€ EUR</option>
                                <option value="GBP">£ GBP</option>
                                <option value="INR">₹ INR</option>
                                <option value="JPY">¥ JPY</option>
                                <option value="AUD">A$ AUD</option>
                                <option value="CAD">C$ CAD</option>
                                <option value="SGD">S$ SGD</option>
                                <option value="AED">د.إ AED</option>
                                <option value="THB">฿ THB</option>
                            </select>
                            <input type="number" name="budget" x-model="form.budget" placeholder="Total budget..." min="0"
                                class="flex-1 border border-journal-border py-3 px-4 bg-white text-journal-dark focus:outline-none focus:border-journal-accent">
                        </div>
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-xs font-bold uppercase tracking-wider text-journal-light mb-4">Travel Style *</label>
                    <input type="hidden" name="travel_style" :value="form.travel_style">
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        @foreach([
                            ['value'=>'budget','icon'=>'fa-piggy-bank','label'=>'Budget','desc'=>'Stretch every dollar'],
                            ['value'=>'backpacker','icon'=>'fa-person-hiking','label'=>'Backpacker','desc'=>'Hostel hopping & adventures'],
                            ['value'=>'cultural','icon'=>'fa-landmark','label'=>'Cultural','desc'=>'Art, history & heritage'],
                            ['value'=>'adventure','icon'=>'fa-mountain','label'=>'Adventure','desc'=>'Thrills & adrenaline'],
                            ['value'=>'family','icon'=>'fa-house-chimney-user','label'=>'Family','desc'=>'Fun for all ages'],
                            ['value'=>'luxury','icon'=>'fa-gem','label'=>'Luxury','desc'=>'Only the finest'],
                        ] as $style)
                        <div @click="form.travel_style = '{{ $style['value'] }}'"
                            class="border-2 p-4 cursor-pointer transition-all duration-200 text-center"
                            :class="form.travel_style === '{{ $style['value'] }}' ? 'border-journal-accent bg-orange-50' : 'border-journal-border hover:border-journal-accent/50'">
                            <i class="fa-solid {{ $style['icon'] }} text-2xl mb-2" :class="form.travel_style === '{{ $style['value'] }}' ? 'text-journal-accent' : 'text-journal-light'"></i>
                            <div class="font-bold text-sm text-journal-dark">{{ $style['label'] }}</div>
                            <div class="text-xs text-journal-light mt-1">{{ $style['desc'] }}</div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="flex justify-between mt-6">
                <button type="button" @click="step--" class="border-2 border-journal-border text-journal-dark font-bold py-3 px-6 hover:bg-journal-paper transition flex items-center gap-2">
                    <i class="fa-solid fa-arrow-left"></i> Back
                </button>
                <button type="button" @click="nextStep()" :disabled="!form.travel_style"
                    class="bg-journal-dark hover:bg-journal-accent text-white font-bold py-3 px-8 transition disabled:opacity-50 flex items-center gap-2">
                    Continue <i class="fa-solid fa-arrow-right"></i>
                </button>
            </div>
        </div>

        {{-- =================== STEP 3: AI Preferences =================== --}}
        <div x-show="step === 3" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">
            <div class="bg-white border border-journal-border shadow-postcard p-8">
                <h2 class="text-2xl font-serif font-bold text-journal-dark mb-2 flex items-center gap-3">
                    <i class="fa-solid fa-wand-magic-sparkles text-journal-gold"></i> Personalize Your Experience
                </h2>
                <p class="text-journal-light text-sm mb-6">These preferences help our AI craft the perfect itinerary for you.</p>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-journal-light mb-2">Food Preferences</label>
                        <select name="food_preferences" x-model="form.food_preferences" class="w-full border border-journal-border py-3 px-4 bg-white focus:outline-none focus:border-journal-accent">
                            <option value="">Any cuisine</option>
                            <option value="vegetarian">Vegetarian</option>
                            <option value="vegan">Vegan</option>
                            <option value="halal">Halal</option>
                            <option value="local_cuisine">Local street food</option>
                            <option value="fine_dining">Fine dining</option>
                            <option value="seafood">Seafood lover</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-journal-light mb-2">Accommodation</label>
                        <select name="accommodation_type" x-model="form.accommodation_type" class="w-full border border-journal-border py-3 px-4 bg-white focus:outline-none focus:border-journal-accent">
                            <option value="hotel">Hotel</option>
                            <option value="boutique">Boutique hotel</option>
                            <option value="hostel">Hostel</option>
                            <option value="apartment">Apartment / Airbnb</option>
                            <option value="resort">Resort</option>
                            <option value="camping">Camping</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-journal-light mb-2">Transportation</label>
                        <select name="transportation_preference" x-model="form.transportation_preference" class="w-full border border-journal-border py-3 px-4 bg-white focus:outline-none focus:border-journal-accent">
                            <option value="public">Public transport</option>
                            <option value="rental_car">Rental car</option>
                            <option value="taxi_rideshare">Taxi / Rideshare</option>
                            <option value="bicycle">Bicycle</option>
                            <option value="walking">Walking (city)</option>
                            <option value="mix">Mix of options</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-journal-light mb-3">Interests & Activities</label>
                    <div class="flex flex-wrap gap-2">
                        @foreach(['History & Culture','Art & Museums','Food & Culinary','Adventure & Hiking','Beach & Water','Shopping','Nightlife','Photography','Wildlife','Architecture','Wellness & Spa','Local Markets','Music & Festivals','Sports'] as $interest)
                        <div class="interest-chip">
                            <input type="checkbox" name="interests[]" value="{{ $interest }}" id="int_{{ Str::slug($interest) }}"
                                class="sr-only" :checked="form.interests.includes('{{ $interest }}')"
                                @change="toggleInterest('{{ $interest }}')">
                            <label for="int_{{ Str::slug($interest) }}">{{ $interest }}</label>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="mt-6">
                    <label class="block text-xs font-bold uppercase tracking-wider text-journal-light mb-2">Additional Notes</label>
                    <textarea name="notes" rows="3" placeholder="Any special requirements, accessibility needs, or notes for the AI..."
                        class="w-full border border-journal-border py-3 px-4 bg-white focus:outline-none focus:border-journal-accent resize-none text-sm"></textarea>
                </div>
            </div>

            <div class="flex justify-between mt-6">
                <button type="button" @click="step--" class="border-2 border-journal-border text-journal-dark font-bold py-3 px-6 hover:bg-journal-paper transition flex items-center gap-2">
                    <i class="fa-solid fa-arrow-left"></i> Back
                </button>
                <button type="button" @click="step++"
                    class="bg-journal-dark hover:bg-journal-accent text-white font-bold py-3 px-8 transition flex items-center gap-2">
                    Continue <i class="fa-solid fa-arrow-right"></i>
                </button>
            </div>
        </div>

        {{-- =================== STEP 4: Budget Mode & Submit =================== --}}
        <div x-show="step === 4" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">
            <div class="bg-white border border-journal-border shadow-postcard p-8">
                <h2 class="text-2xl font-serif font-bold text-journal-dark mb-2 flex items-center gap-3">
                    <i class="fa-solid fa-piggy-bank text-journal-accent"></i> Budget Mode
                </h2>
                <p class="text-journal-light text-sm mb-8">Choose how you'd like our AI to optimize your travel experience.</p>

                <input type="hidden" name="budget_mode" :value="form.budget_mode">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div @click="form.budget_mode = 'standard'"
                        class="border-2 p-6 cursor-pointer transition-all duration-200"
                        :class="form.budget_mode === 'standard' ? 'border-journal-accent bg-orange-50' : 'border-journal-border hover:border-journal-accent/50'">
                        <div class="flex items-center gap-3 mb-3">
                            <i class="fa-solid fa-star text-2xl" :class="form.budget_mode === 'standard' ? 'text-journal-accent' : 'text-journal-light'"></i>
                            <h3 class="font-bold text-lg font-serif text-journal-dark">Standard Mode</h3>
                        </div>
                        <p class="text-journal-light text-sm">Balanced recommendations across comfort, quality, and cost. Best of all worlds.</p>
                        <ul class="mt-4 space-y-1 text-sm text-journal-dark">
                            <li class="flex gap-2"><i class="fa-solid fa-check text-journal-olive mt-0.5"></i> Mix of budget & comfort stays</li>
                            <li class="flex gap-2"><i class="fa-solid fa-check text-journal-olive mt-0.5"></i> Varied dining options</li>
                            <li class="flex gap-2"><i class="fa-solid fa-check text-journal-olive mt-0.5"></i> Popular attractions</li>
                        </ul>
                    </div>
                    <div @click="form.budget_mode = 'budget_friendly'"
                        class="border-2 p-6 cursor-pointer transition-all duration-200"
                        :class="form.budget_mode === 'budget_friendly' ? 'border-journal-olive bg-green-50' : 'border-journal-border hover:border-journal-olive/50'">
                        <div class="flex items-center gap-3 mb-3">
                            <i class="fa-solid fa-piggy-bank text-2xl" :class="form.budget_mode === 'budget_friendly' ? 'text-journal-olive' : 'text-journal-light'"></i>
                            <h3 class="font-bold text-lg font-serif text-journal-dark">Budget Friendly</h3>
                        </div>
                        <p class="text-journal-light text-sm">AI optimizes for maximum savings without sacrificing the experience.</p>
                        <ul class="mt-4 space-y-1 text-sm text-journal-dark">
                            <li class="flex gap-2"><i class="fa-solid fa-check text-journal-olive mt-0.5"></i> Cheapest transport routes</li>
                            <li class="flex gap-2"><i class="fa-solid fa-check text-journal-olive mt-0.5"></i> Street food & local eats</li>
                            <li class="flex gap-2"><i class="fa-solid fa-check text-journal-olive mt-0.5"></i> Free & low-cost activities</li>
                            <li class="flex gap-2"><i class="fa-solid fa-piggy-bank text-journal-olive mt-0.5"></i> Save up to 55% on costs</li>
                        </ul>
                    </div>
                </div>

                {{-- Summary Preview --}}
                <div class="bg-journal-paper border border-journal-border p-6">
                    <h3 class="font-bold font-serif text-journal-dark mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-clipboard-list text-journal-accent"></i> Trip Summary
                    </h3>
                    <dl class="grid grid-cols-2 gap-3 text-sm">
                        <div><dt class="text-journal-light text-xs uppercase font-bold">Title</dt><dd class="text-journal-dark font-medium" x-text="form.title || '—'"></dd></div>
                        <div><dt class="text-journal-light text-xs uppercase font-bold">Dates</dt><dd class="text-journal-dark font-medium" x-text="form.dates || '—'"></dd></div>
                        <div><dt class="text-journal-light text-xs uppercase font-bold">Travelers</dt><dd class="text-journal-dark font-medium" x-text="form.num_travelers + ' people'"></dd></div>
                        <div><dt class="text-journal-light text-xs uppercase font-bold">Style</dt><dd class="text-journal-dark font-medium capitalize" x-text="form.travel_style || '—'"></dd></div>
                        <div><dt class="text-journal-light text-xs uppercase font-bold">Budget</dt><dd class="text-journal-dark font-medium" x-text="form.budget ? form.currency + ' ' + Number(form.budget).toLocaleString() : 'Not set'"></dd></div>
                        <div><dt class="text-journal-light text-xs uppercase font-bold">Mode</dt><dd class="text-journal-dark font-medium" x-text="form.budget_mode === 'budget_friendly' ? '💰 Budget Friendly' : '⭐ Standard'"></dd></div>
                    </dl>
                </div>
            </div>

            <div class="flex justify-between mt-6">
                <button type="button" @click="step--" class="border-2 border-journal-border text-journal-dark font-bold py-3 px-6 hover:bg-journal-paper transition flex items-center gap-2">
                    <i class="fa-solid fa-arrow-left"></i> Back
                </button>
                <button type="submit" id="submitBtn"
                    class="bg-journal-accent hover:bg-journal-dark text-white font-bold py-4 px-10 transition flex items-center gap-3 shadow-md text-lg">
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                    <span x-text="'{{ $aiAvailable }}' === '1' ? 'Generate AI Itinerary' : 'Create My Itinerary'"></span>
                </button>
            </div>
        </div>

    </form>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
// ── tripWizard Alpine component ──────────────────────────────────────────────
function tripWizard() {
    return {
        step: 1,
        form: {
            title: '{{ old("title", "") }}',
            destination_id: '{{ old("destination_id", $destination?->id ?? "") }}',
            dates: '{{ old("dates", "") }}',
            num_travelers: {{ old("num_travelers", 1) }},
            budget: '{{ old("budget", "") }}',
            currency: '{{ old("currency", "USD") }}',
            travel_style: '{{ old("travel_style", "budget") }}',
            food_preferences: '{{ old("food_preferences", "") }}',
            accommodation_type: '{{ old("accommodation_type", "hotel") }}',
            transportation_preference: '{{ old("transportation_preference", "public") }}',
            budget_mode: '{{ old("budget_mode", "standard") }}',
            interests: [],
        },
        nextStep() {
            if (this.step === 1 && (!this.form.destination_id || !this.form.title || !this.form.dates)) return;
            if (this.step === 2 && !this.form.travel_style) return;
            this.step++;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },
        toggleInterest(interest) {
            const idx = this.form.interests.indexOf(interest);
            if (idx > -1) this.form.interests.splice(idx, 1);
            else this.form.interests.push(interest);
        },
    };
}

// ── Flatpickr initialisation (runs after Alpine has booted) ──────────────────
document.addEventListener('alpine:initialized', () => {
    // Get Alpine component root so we can write to its reactive data directly
    const wizardEl = document.querySelector('[x-data="tripWizard()"]');

    flatpickr('#datepicker', {
        mode: 'range',
        minDate: 'today',
        dateFormat: 'Y-m-d',
        defaultDate: '{{ old("dates") }}' || null,
        onChange(selectedDates, dateStr) {
            // Push value directly into Alpine reactive state
            if (wizardEl) {
                Alpine.$data(wizardEl).form.dates = dateStr;
            }
        },
    });
});

// ── Loading state on submit ───────────────────────────────────────────────────
document.getElementById('tripForm').addEventListener('submit', function () {
    const btn = document.getElementById('submitBtn');
    if (btn) {
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Generating your journey...';
        btn.disabled = true;
    }
});
</script>
@endpush
