<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DestinationController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ItineraryController;
use App\Http\Controllers\PackingListController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TripController;
use App\Http\Controllers\WeatherController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/destinations', [DestinationController::class, 'index'])->name('destinations.index');
Route::get('/destinations/{destination:slug}', [DestinationController::class, 'show'])->name('destinations.show');

Route::get('/api/weather', [WeatherController::class, 'getForecast'])->name('api.weather');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('trips', TripController::class);
    
    Route::get('/trips/{trip}/itinerary', [ItineraryController::class, 'show'])->name('trips.itinerary.show');
    Route::post('/trips/{trip}/itinerary/reorder', [ItineraryController::class, 'reorder'])->name('trips.itinerary.reorder');
    Route::post('/trips/{trip}/itinerary/days/{day}/items', [ItineraryController::class, 'storeItem'])->name('trips.itinerary.items.store');
    Route::delete('/trips/{trip}/itinerary/items/{item}', [ItineraryController::class, 'destroyItem'])->name('trips.itinerary.items.destroy');
    
    Route::get('/trips/{trip}/packing', [PackingListController::class, 'show'])->name('trips.packing.show');
    Route::post('/trips/{trip}/packing', [PackingListController::class, 'store'])->name('trips.packing.store');
    Route::patch('/trips/{trip}/packing/{item}/toggle', [PackingListController::class, 'toggle'])->name('trips.packing.toggle');
    Route::delete('/trips/{trip}/packing/{item}', [PackingListController::class, 'destroy'])->name('trips.packing.destroy');
    
    Route::get('/trips/{trip}/pdf', [PdfController::class, 'exportItinerary'])->name('trips.pdf');
    
    Route::resource('bookings', BookingController::class)->except(['create', 'show', 'edit', 'update']);

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
