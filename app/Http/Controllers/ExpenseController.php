<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Trip;
use App\Http\Requests\StoreExpenseRequest;
use App\Services\BudgetOptimizationService;
use App\Services\CurrencyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ExpenseController extends Controller
{
    public function __construct(
        private BudgetOptimizationService $budget,
        private CurrencyService $currency
    ) {}

    /**
     * Expense tracker for a specific trip.
     */
    public function index(Trip $trip)
    {
        $this->authorize('view', [Expense::class, $trip]);

        $trip->load(['expenses' => fn($q) => $q->orderBy('expense_date', 'desc')]);

        $analytics = $this->budget->analyzeSpendings($trip);
        $budgetTips = $trip->budget_mode === 'budget_friendly'
            ? $this->budget->getBudgetFriendlyTips($trip)
            : null;

        $currencies = $this->currency->getSupportedCurrencies();

        return view('expenses.index', compact('trip', 'analytics', 'budgetTips', 'currencies'));
    }

    /**
     * Store a new expense.
     */
    public function store(StoreExpenseRequest $request, Trip $trip)
    {
        $this->authorize('create', [Expense::class, $trip]);

        $validated = $request->validated();

        $validated['trip_id'] = $trip->id;
        $validated['user_id'] = Auth::id();

        Expense::create($validated);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Expense added']);
        }

        return back()->with('success', 'Expense recorded successfully!');
    }

    /**
     * Delete an expense.
     */
    public function destroy(Trip $trip, Expense $expense)
    {
        $this->authorize('delete', $expense); $expense->delete();

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Expense removed');
    }

    /**
     * Get analytics data for Chart.js (AJAX).
     */
    public function analytics(Trip $trip)
    {
        $this->authorize('view', [Expense::class, $trip]);

        $analytics = Cache::remember(
            "trip_{$trip->id}_analytics",
            now()->addHours(24),
            fn() => $this->budget->analyzeSpendings($trip)
        );

        // Daily spending series
        $dailyData = $trip->expenses()
            ->selectRaw('expense_date, SUM(amount) as total')
            ->groupBy('expense_date')
            ->orderBy('expense_date')
            ->get()
            ->map(fn($row) => ['date' => $row->expense_date->format('M d'), 'amount' => $row->total]);

        return response()->json([
            'analytics'  => $analytics,
            'daily_data' => $dailyData,
        ]);
    }

    /**
     * Currency conversion endpoint (AJAX).
     */
    public function convert(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'from'   => 'required|string|size:3',
            'to'     => 'required|string|size:3',
        ]);

        $converted = $this->currency->convert(
            $request->amount, $request->from, $request->to
        );

        return response()->json([
            'original'  => $request->amount,
            'converted' => $converted,
            'from'      => $request->from,
            'to'        => $request->to,
            'formatted' => $this->currency->format($converted, $request->to),
        ]);
    }
}
