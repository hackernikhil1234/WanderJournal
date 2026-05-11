<?php

use App\Http\Controllers\AiPlannerController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CommunityController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DestinationController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ItineraryController;
use App\Http\Controllers\PackingListController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TripController;
use App\Http\Controllers\WeatherController;
use Illuminate\Support\Facades\Route;

// === Public Routes ===
Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/destinations', [DestinationController::class, 'index'])->name('destinations.index');
Route::get('/destinations/{destination:slug}', [DestinationController::class, 'show'])->name('destinations.show');

Route::get('/api/weather', [WeatherController::class, 'getForecast'])->name('api.weather');

// Community (public feed — viewable by all)
Route::get('/explore', [CommunityController::class, 'feed'])->name('community.feed');
Route::get('/explore/{post}', [CommunityController::class, 'show'])->name('community.show');
Route::get('/travelers/{user}', [CommunityController::class, 'profile'])->name('community.profile');

// === Authenticated Routes ===
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Trips (full resource + extras)
    Route::resource('trips', TripController::class);
    Route::post('/trips/{trip}/clone', [TripController::class, 'clone'])->name('trips.clone');

    // Itinerary
    Route::get('/trips/{trip}/itinerary', [ItineraryController::class, 'show'])->name('trips.itinerary.show');
    Route::post('/trips/{trip}/itinerary/reorder', [ItineraryController::class, 'reorder'])->name('trips.itinerary.reorder');
    Route::post('/trips/{trip}/itinerary/days/{day}/items', [ItineraryController::class, 'storeItem'])->name('trips.itinerary.items.store');
    Route::delete('/trips/{trip}/itinerary/items/{item}', [ItineraryController::class, 'destroyItem'])->name('trips.itinerary.items.destroy');

    // Packing
    Route::get('/trips/{trip}/packing', [PackingListController::class, 'show'])->name('trips.packing.show');
    Route::post('/trips/{trip}/packing', [PackingListController::class, 'store'])->name('trips.packing.store');
    Route::patch('/trips/{trip}/packing/{item}/toggle', [PackingListController::class, 'toggle'])->name('trips.packing.toggle');
    Route::delete('/trips/{trip}/packing/{item}', [PackingListController::class, 'destroy'])->name('trips.packing.destroy');
    Route::post('/trips/{trip}/packing/generate-ai', [PackingListController::class, 'generateAI'])->name('trips.packing.generate-ai');

    // PDF Export
    Route::get('/trips/{trip}/pdf', [PdfController::class, 'exportItinerary'])->name('trips.pdf');

    // Bookings
    Route::resource('bookings', BookingController::class)->except(['create', 'show', 'edit', 'update']);

    // Expenses (per-trip)
    Route::get('/trips/{trip}/expenses', [ExpenseController::class, 'index'])->name('trips.expenses.index');
    Route::post('/trips/{trip}/expenses', [ExpenseController::class, 'store'])->name('trips.expenses.store');
    Route::delete('/trips/{trip}/expenses/{expense}', [ExpenseController::class, 'destroy'])->name('trips.expenses.destroy');
    Route::get('/trips/{trip}/expenses/analytics', [ExpenseController::class, 'analytics'])->name('trips.expenses.analytics');

    // Currency conversion (AJAX)
    Route::post('/api/currency/convert', [ExpenseController::class, 'convert'])->name('api.currency.convert');

    // AI Planner endpoints (Rate limited to prevent quota exhaustion)
    Route::middleware('throttle:api')->group(function () {
        Route::post('/trips/{trip}/ai/regenerate', [AiPlannerController::class, 'regenerate'])->name('trips.ai.regenerate');
        Route::get('/trips/{trip}/ai/status', [AiPlannerController::class, 'getGenerationStatus'])->name('trips.ai.status');
        Route::get('/trips/{trip}/ai/tips', [AiPlannerController::class, 'getTravelTips'])->name('trips.ai.tips');
        Route::get('/trips/{trip}/ai/hotels', [AiPlannerController::class, 'getHotelRecommendations'])->name('trips.ai.hotels');
        Route::get('/trips/{trip}/ai/budget', [AiPlannerController::class, 'getBudgetBreakdown'])->name('trips.ai.budget');
        Route::post('/api/ai/chat', [AiPlannerController::class, 'chat'])->name('api.ai.chat');
    });

    // Social / Community (auth required for posting)
    Route::get('/explore/create', [CommunityController::class, 'create'])->name('community.create');
    Route::post('/explore', [CommunityController::class, 'store'])->name('community.store');
    Route::post('/explore/{post}/like', [CommunityController::class, 'like'])->name('community.like');
    Route::post('/explore/{post}/comment', [CommunityController::class, 'comment'])->name('community.comment');
    Route::post('/travelers/{user}/follow', [CommunityController::class, 'follow'])->name('community.follow');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
