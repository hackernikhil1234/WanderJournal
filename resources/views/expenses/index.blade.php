@extends('layouts.app')
@section('title', 'Expense Tracker — {{ $trip->title }}')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-10" x-data="expenseTracker(@json($analytics))">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row justify-between items-start gap-4 mb-8">
        <div>
            <span class="font-script text-2xl text-journal-accent block mb-1">💰 Budget Tracker</span>
            <h1 class="text-3xl font-serif font-bold text-journal-dark">{{ $trip->title }}</h1>
            <p class="text-journal-light mt-1">{{ $trip->destination->name }}, {{ $trip->destination->country }}</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('trips.itinerary.show', $trip) }}" class="border border-journal-border text-journal-dark px-4 py-2 text-sm font-bold hover:bg-journal-paper transition flex items-center gap-2">
                <i class="fa-solid fa-map"></i> Itinerary
            </a>
            <button @click="showAdd = true" class="bg-journal-accent text-white px-5 py-2 font-bold hover:bg-journal-dark transition flex items-center gap-2">
                <i class="fa-solid fa-plus"></i> Add Expense
            </button>
        </div>
    </div>

    {{-- Budget Overview --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white border border-journal-border p-5 text-center shadow-sm">
            <div class="text-3xl font-serif font-bold text-journal-dark">${{ number_format($analytics['total_spent'], 2) }}</div>
            <div class="text-xs uppercase tracking-wider font-bold text-journal-light mt-1">Total Spent</div>
        </div>
        <div class="bg-white border border-journal-border p-5 text-center shadow-sm">
            <div class="text-3xl font-serif font-bold {{ $analytics['remaining'] < 0 ? 'text-red-500' : 'text-journal-olive' }}">
                ${{ number_format(abs($analytics['remaining']), 2) }}
            </div>
            <div class="text-xs uppercase tracking-wider font-bold text-journal-light mt-1">{{ $analytics['remaining'] < 0 ? 'Over Budget' : 'Remaining' }}</div>
        </div>
        <div class="bg-white border border-journal-border p-5 text-center shadow-sm">
            <div class="text-3xl font-serif font-bold text-journal-dark">${{ number_format($analytics['daily_average'], 2) }}</div>
            <div class="text-xs uppercase tracking-wider font-bold text-journal-light mt-1">Daily Average</div>
        </div>
        <div class="bg-white border border-journal-border p-5 text-center shadow-sm">
            <div class="text-3xl font-serif font-bold {{ $analytics['percentage_used'] > 90 ? 'text-red-500' : 'text-journal-dark' }}">
                {{ $analytics['percentage_used'] }}%
            </div>
            <div class="text-xs uppercase tracking-wider font-bold text-journal-light mt-1">Budget Used</div>
        </div>
    </div>

    {{-- Budget Bar --}}
    @if($analytics['budget'] > 0)
    <div class="bg-white border border-journal-border p-5 mb-8 shadow-sm">
        <div class="flex justify-between text-sm font-bold mb-2">
            <span class="text-journal-dark">Budget: ${{ number_format($analytics['budget'], 2) }}</span>
            <span class="{{ $analytics['percentage_used'] > 80 ? 'text-red-500' : 'text-journal-olive' }}">{{ $analytics['percentage_used'] }}% used</span>
        </div>
        <div class="w-full bg-gray-200 h-4 rounded-full overflow-hidden">
            <div class="h-4 rounded-full transition-all duration-700 {{ $analytics['percentage_used'] > 90 ? 'bg-red-500' : ($analytics['percentage_used'] > 75 ? 'bg-yellow-500' : 'bg-journal-olive') }}"
                style="width: {{ min(100, $analytics['percentage_used']) }}%"></div>
        </div>

        {{-- Alerts --}}
        @foreach($analytics['alerts'] as $alert)
        <div class="mt-3 text-sm flex items-center gap-2 {{ $alert['type'] === 'danger' ? 'text-red-600' : 'text-amber-700' }}">
            <i class="fa-solid fa-triangle-exclamation"></i> {{ $alert['message'] }}
        </div>
        @endforeach
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Category Breakdown + Chart --}}
        <div class="lg:col-span-1">
            <div class="bg-white border border-journal-border p-6 shadow-sm mb-6">
                <h3 class="font-serif font-bold text-journal-dark mb-4">By Category</h3>
                <div id="categoryDonut" style="height:200px;"></div>
                <div class="mt-4 space-y-2">
                    @foreach($analytics['by_category'] as $cat)
                    <div class="flex items-center justify-between text-sm">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid {{ $cat['icon'] }} w-4" style="color:{{ $cat['color'] }}"></i>
                            <span class="capitalize text-journal-dark">{{ $cat['category'] }}</span>
                        </div>
                        <span class="font-bold text-journal-dark">${{ number_format($cat['total'], 2) }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Currency Converter --}}
            <div class="bg-journal-paper border border-journal-border p-5 shadow-sm" x-data="currencyConverter()">
                <h3 class="font-serif font-bold text-journal-dark mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-arrows-rotate text-journal-accent"></i> Currency Converter
                </h3>
                <div class="flex gap-2 mb-2">
                    <input type="number" x-model="amount" @input="convert()" placeholder="Amount"
                        class="flex-1 border border-journal-border py-2 px-3 text-sm bg-white focus:outline-none">
                    <select x-model="from" @change="convert()" class="border border-journal-border py-2 px-2 text-sm bg-white focus:outline-none">
                        @foreach($currencies as $code => $info)
                        <option value="{{ $code }}">{{ $code }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="text-center text-journal-light text-xs my-2">↓ converts to ↓</div>
                <div class="flex gap-2">
                    <div class="flex-1 border border-journal-border py-2 px-3 text-sm bg-white font-bold text-journal-dark" x-text="result || '—'"></div>
                    <select x-model="to" @change="convert()" class="border border-journal-border py-2 px-2 text-sm bg-white focus:outline-none">
                        @foreach($currencies as $code => $info)
                        <option value="{{ $code }}" {{ $code === 'INR' ? 'selected' : '' }}>{{ $code }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Expenses List --}}
        <div class="lg:col-span-2">
            <div class="bg-white border border-journal-border shadow-sm">
                <div class="p-5 border-b border-journal-border flex justify-between items-center">
                    <h3 class="font-serif font-bold text-journal-dark">All Expenses</h3>
                    <span class="text-xs text-journal-light">{{ $trip->expenses->count() }} records</span>
                </div>
                @if($trip->expenses->isEmpty())
                <div class="p-10 text-center">
                    <i class="fa-solid fa-receipt text-4xl text-journal-light mb-3"></i>
                    <p class="text-journal-light">No expenses tracked yet. Add your first expense!</p>
                </div>
                @else
                <div class="divide-y divide-journal-border">
                    @foreach($trip->expenses as $expense)
                    <div class="flex items-center gap-4 p-4 hover:bg-journal-paper/50 transition group">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0"
                            style="background: {{ $expense->category_color }}22; color: {{ $expense->category_color }}">
                            <i class="fa-solid {{ $expense->category_icon }} text-sm"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="font-medium text-journal-dark truncate">{{ $expense->title }}</div>
                            <div class="text-xs text-journal-light">{{ ucfirst($expense->category) }} · {{ $expense->expense_date->format('M d, Y') }}</div>
                        </div>
                        <div class="font-bold text-journal-dark text-right flex-shrink-0">
                            {{ $expense->currency }} {{ number_format($expense->amount, 2) }}
                        </div>
                        <form method="POST" action="{{ route('trips.expenses.destroy', [$trip, $expense]) }}" class="opacity-0 group-hover:opacity-100 transition"
                            onsubmit="return confirm('Delete this expense?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-400 hover:text-red-600 text-sm"><i class="fa-solid fa-trash"></i></button>
                        </form>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Add Expense Modal --}}
<div x-show="showAdd" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
    <div class="bg-white max-w-md w-full shadow-2xl" @click.outside="showAdd = false">
        <div class="bg-journal-dark text-white p-5 flex justify-between items-center">
            <h3 class="font-serif font-bold text-lg">Add Expense</h3>
            <button @click="showAdd = false"><i class="fa-solid fa-times"></i></button>
        </div>
        <form method="POST" action="{{ route('trips.expenses.store', $trip) }}" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="text-xs font-bold uppercase tracking-wider text-journal-light block mb-1">Title *</label>
                <input type="text" name="title" required placeholder="e.g. Hotel breakfast" class="w-full border border-journal-border py-2 px-3 focus:outline-none focus:border-journal-accent">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-xs font-bold uppercase tracking-wider text-journal-light block mb-1">Category *</label>
                    <select name="category" required class="w-full border border-journal-border py-2 px-3 focus:outline-none">
                        <option value="accommodation">🏨 Accommodation</option>
                        <option value="food">🍜 Food</option>
                        <option value="transport">🚌 Transport</option>
                        <option value="activities">🎟️ Activities</option>
                        <option value="shopping">🛍️ Shopping</option>
                        <option value="health">💊 Health</option>
                        <option value="communication">📶 Communication</option>
                        <option value="other">📦 Other</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs font-bold uppercase tracking-wider text-journal-light block mb-1">Date *</label>
                    <input type="date" name="expense_date" required value="{{ date('Y-m-d') }}" class="w-full border border-journal-border py-2 px-3 focus:outline-none">
                </div>
            </div>
            <div class="flex gap-2">
                <select name="currency" class="border border-journal-border py-2 px-3 focus:outline-none">
                    @foreach($currencies as $code => $info)
                    <option value="{{ $code }}" {{ $code === $trip->currency ? 'selected' : '' }}>{{ $code }}</option>
                    @endforeach
                </select>
                <input type="number" name="amount" step="0.01" min="0" required placeholder="0.00"
                    class="flex-1 border border-journal-border py-2 px-3 focus:outline-none focus:border-journal-accent">
            </div>
            <div>
                <label class="text-xs font-bold uppercase tracking-wider text-journal-light block mb-1">Notes</label>
                <textarea name="notes" rows="2" placeholder="Optional notes..." class="w-full border border-journal-border py-2 px-3 focus:outline-none resize-none text-sm"></textarea>
            </div>
            <button type="submit" class="w-full bg-journal-accent text-white py-3 font-bold hover:bg-journal-dark transition flex items-center justify-center gap-2">
                <i class="fa-solid fa-plus"></i> Add Expense
            </button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
function expenseTracker(analytics) {
    return { showAdd: false, analytics };
}
function currencyConverter() {
    return {
        amount: 100, from: '{{ $trip->currency }}', to: 'INR', result: '',
        async convert() {
            if (!this.amount) return;
            const r = await fetch('/api/currency/convert', {
                method: 'POST',
                headers: {'Content-Type':'application/json','X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content},
                body: JSON.stringify({amount: this.amount, from: this.from, to: this.to})
            });
            const data = await r.json();
            this.result = data.formatted;
        }
    };
}
@if($analytics['by_category'])
const cats = @json($analytics['by_category']);
if (cats.length > 0) {
    new ApexCharts(document.getElementById('categoryDonut'), {
        chart: { type: 'donut', height: 200, fontFamily: '"Lato", sans-serif' },
        series: cats.map(c => c.total),
        labels: cats.map(c => c.category.charAt(0).toUpperCase() + c.category.slice(1)),
        colors: cats.map(c => c.color),
        legend: { position: 'bottom', fontSize: '10px' },
        dataLabels: { enabled: false },
        plotOptions: { pie: { donut: { size: '70%' } } },
        tooltip: { y: { formatter: v => '$' + v.toFixed(2) } },
    }).render();
}
@endif
</script>
@endpush
