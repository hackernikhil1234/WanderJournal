<?php

namespace App\Services;

use App\Models\Trip;
use App\Models\ItineraryDay;
use App\Models\ItineraryItem;
use App\Models\Destination;
use App\Models\PackingItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ItineraryGeneratorService
{
    public function __construct(private GeminiAIService $ai) {}

    /**
     * Generate a complete smart itinerary for a trip.
     * Tries AI first, falls back to rule-based generation.
     */
    public function generateForTrip(Trip $trip): void
    {
        $trip->itineraryDays()->delete();

        if ($this->ai->isConfigured()) {
            $this->generateWithAI($trip);
        } else {
            $this->generateRuleBased($trip);
        }

        // Always generate packing list
        $this->generatePackingList($trip);
    }

    /**
     * AI-powered itinerary generation.
     */
    private function generateWithAI(Trip $trip): void
    {
        $aiData = $this->ai->generateItinerary($trip);

        if (!$aiData || empty($aiData['days'])) {
            Log::info('AI itinerary generation returned empty, falling back to rule-based.');
            $this->generateRuleBased($trip);
            return;
        }

        // Store AI summary
        $trip->update([
            'ai_generated' => true,
            'ai_summary'   => $aiData['trip_summary'] ?? null,
            'estimated_cost' => $aiData['estimated_total_cost'] ?? null,
            'ai_metadata' => json_encode([
                'model' => env('GEMINI_MODEL', 'gemini-1.5-flash'),
                'generated_at' => now()->toIsoString(),
                'top_tips' => $aiData['top_tips'] ?? [],
                'best_restaurants' => $aiData['best_restaurants'] ?? [],
                'nearby_attractions' => $aiData['nearby_attractions'] ?? [],
            ]),
        ]);

        // Create days and items from AI response
        foreach ($aiData['days'] as $dayData) {
            $dayNumber = $dayData['day_number'];
            $dateStr = $dayData['date'] ?? $trip->start_date->copy()->addDays($dayNumber - 1)->format('Y-m-d');

            $day = ItineraryDay::create([
                'trip_id'    => $trip->id,
                'day_number' => $dayNumber,
                'date'       => $dateStr,
                'title'      => $dayData['title'] ?? "Day {$dayNumber}",
                'notes'      => ($dayData['daily_tip'] ?? '') . ' | ' . ($dayData['weather_note'] ?? ''),
            ]);

            $sortOrder = 1;
            foreach ($dayData['items'] ?? [] as $item) {
                $rawType = strtolower($item['type'] ?? 'attraction');
                $typeMap = [
                    'dining' => 'restaurant', 'food' => 'restaurant', 'meal' => 'restaurant',
                    'lodging' => 'hotel', 'accommodation' => 'hotel', 'stay' => 'hotel',
                    'transit' => 'transport', 'travel' => 'transport', 'flight' => 'transport', 'train' => 'transport',
                    'tour' => 'activity', 'experience' => 'activity', 'entertainment' => 'activity',
                    'museum' => 'attraction', 'park' => 'attraction', 'sightseeing' => 'attraction',
                    'store' => 'shopping', 'market' => 'shopping', 'retail' => 'shopping',
                ];
                $mappedType = $typeMap[$rawType] ?? $rawType;
                
                $allowedTypes = ['attraction', 'restaurant', 'hotel', 'transport', 'activity', 'shopping', 'other'];
                if (!in_array($mappedType, $allowedTypes)) {
                    $mappedType = 'other';
                }

                ItineraryItem::create([
                    'itinerary_day_id' => $day->id,
                    'title'            => $item['title'] ?? 'Activity',
                    'description'      => $item['description'] ?? null,
                    'location'         => $item['location'] ?? $trip->destination->name,
                    'start_time'       => $item['time'] ?? null,
                    'end_time'         => $item['end_time'] ?? null,
                    'type'             => $mappedType,
                    'cost'             => ($item['cost_per_person'] ?? 0) * $trip->num_travelers,
                    'notes'            => $item['insider_tip'] ?? null,
                    'sort_order'       => $sortOrder++,
                ]);
            }
        }
    }

    /**
     * Rule-based itinerary generation (fallback when AI is unavailable).
     */
    private function generateRuleBased(Trip $trip): void
    {
        $startDate = Carbon::parse($trip->start_date);
        $numDays = $trip->num_days;

        $days = [];
        for ($i = 1; $i <= $numDays; $i++) {
            $currentDate = $startDate->copy()->addDays($i - 1);
            $days[] = ItineraryDay::create([
                'trip_id'    => $trip->id,
                'day_number' => $i,
                'date'       => $currentDate,
                'title'      => $this->generateDayTitle($i, $numDays),
            ]);
        }

        $this->populateDayItems($trip, $days);
    }

    protected function generateDayTitle(int $dayNumber, int $totalDays): string
    {
        if ($dayNumber === 1) return 'Arrival & Orientation';
        if ($dayNumber === $totalDays) return 'Final Day & Departure';

        $titles = [
            'Exploring the City Center',
            'Cultural Immersion',
            'Adventure & Nature',
            'Local Cuisine & Shopping',
            'Historical Highlights',
            'Hidden Gems & Local Life',
            'Relaxation & Leisure',
        ];

        return $titles[($dayNumber - 2) % count($titles)];
    }

    protected function populateDayItems(Trip $trip, array $days): void
    {
        $destination = $trip->destination;
        $highlights = $destination->highlights ?? $this->getMockHighlights($destination);
        $budgetFactor = $this->getBudgetFactor($trip->travel_style);

        $highlightIndex = 0;

        foreach ($days as $index => $day) {
            $isArrivalDay   = ($index === 0);
            $isDepartureDay = ($index === count($days) - 1);
            $sortOrder = 1;

            if (!$isArrivalDay) {
                ItineraryItem::create([
                    'itinerary_day_id' => $day->id,
                    'title'     => 'Breakfast at local café',
                    'start_time' => '08:30',
                    'end_time'   => '09:30',
                    'type'       => 'restaurant',
                    'cost'       => 12 * $budgetFactor * $trip->num_travelers,
                    'sort_order' => $sortOrder++,
                ]);

                $activity = $highlights[$highlightIndex % count($highlights)];
                $highlightIndex++;

                ItineraryItem::create([
                    'itinerary_day_id' => $day->id,
                    'title'       => $activity['name'],
                    'description' => $activity['description'],
                    'location'    => $destination->name,
                    'start_time'  => '10:00',
                    'end_time'    => '12:30',
                    'type'        => 'attraction',
                    'cost'        => ($activity['cost'] ?? 25) * $budgetFactor * $trip->num_travelers,
                    'sort_order'  => $sortOrder++,
                ]);
            } else {
                ItineraryItem::create([
                    'itinerary_day_id' => $day->id,
                    'title'     => 'Arrive & check into accommodation',
                    'start_time' => '14:00',
                    'end_time'   => '15:30',
                    'type'       => 'hotel',
                    'cost'       => 0,
                    'sort_order' => $sortOrder++,
                ]);
            }

            ItineraryItem::create([
                'itinerary_day_id' => $day->id,
                'title'     => 'Lunch',
                'start_time' => '13:00',
                'end_time'   => '14:00',
                'type'       => 'restaurant',
                'cost'       => 20 * $budgetFactor * $trip->num_travelers,
                'sort_order' => $sortOrder++,
            ]);

            if (!$isDepartureDay) {
                $activity = $highlights[$highlightIndex % count($highlights)];
                $highlightIndex++;

                ItineraryItem::create([
                    'itinerary_day_id' => $day->id,
                    'title'       => $activity['name'],
                    'description' => $activity['description'],
                    'location'    => $destination->name,
                    'start_time'  => '14:30',
                    'end_time'    => '17:00',
                    'type'        => 'attraction',
                    'cost'        => ($activity['cost'] ?? 20) * $budgetFactor * $trip->num_travelers,
                    'sort_order'  => $sortOrder++,
                ]);

                ItineraryItem::create([
                    'itinerary_day_id' => $day->id,
                    'title'     => 'Dinner & Evening Stroll',
                    'start_time' => '19:00',
                    'end_time'   => '21:00',
                    'type'       => 'restaurant',
                    'cost'       => 40 * $budgetFactor * $trip->num_travelers,
                    'sort_order' => $sortOrder++,
                ]);
            } else {
                ItineraryItem::create([
                    'itinerary_day_id' => $day->id,
                    'title'     => 'Head to airport/station for departure',
                    'start_time' => '15:00',
                    'end_time'   => '16:00',
                    'type'       => 'transport',
                    'cost'       => 30 * $trip->num_travelers,
                    'sort_order' => $sortOrder++,
                ]);
            }
        }
    }

    public function generatePackingList(Trip $trip): void
    {
        $trip->packingItems()->delete();

        // Try AI packing list first
        if ($this->ai->isConfigured()) {
            $aiPacking = $this->ai->generatePackingList($trip);
            if ($aiPacking && !empty($aiPacking['categories'])) {
                $sortOrder = 0;
                $validCategories = ['clothing', 'essentials', 'electronics', 'toiletries', 'documents', 'health', 'entertainment', 'other'];
                
                foreach ($aiPacking['categories'] as $category) {
                    $rawCat = strtolower($category['name'] ?? 'other');
                    $mappedCat = 'other';
                    foreach ($validCategories as $valid) {
                        if (str_contains($rawCat, $valid)) {
                            $mappedCat = $valid;
                            break;
                        }
                    }
                    
                    foreach ($category['items'] ?? [] as $item) {
                        $quantity = intval($item['quantity'] ?? 1);
                        if ($quantity <= 0) $quantity = 1;

                        $trip->packingItems()->create([
                            'name'       => $item['name'],
                            'category'   => $mappedCat,
                            'quantity'   => $quantity,
                            'sort_order' => $sortOrder++,
                        ]);
                    }
                }
                return;
            }
        }

        // Fallback: rule-based packing list
        $this->generateRuleBasedPackingList($trip);
    }

    private function generateRuleBasedPackingList(Trip $trip): void
    {
        $defaultItems = [
            ['name' => 'Passport/ID', 'category' => 'documents', 'quantity' => 1],
            ['name' => 'Tickets & Reservations', 'category' => 'documents', 'quantity' => 1],
            ['name' => 'Travel Insurance', 'category' => 'documents', 'quantity' => 1],
            ['name' => 'Phone & Charger', 'category' => 'electronics', 'quantity' => 1],
            ['name' => 'Universal Adapter', 'category' => 'electronics', 'quantity' => 1],
            ['name' => 'Power Bank', 'category' => 'electronics', 'quantity' => 1],
            ['name' => 'Toothbrush & Paste', 'category' => 'toiletries', 'quantity' => 1],
            ['name' => 'Deodorant', 'category' => 'toiletries', 'quantity' => 1],
            ['name' => 'Shampoo & Conditioner', 'category' => 'toiletries', 'quantity' => 1],
            ['name' => 'First Aid Kit', 'category' => 'health', 'quantity' => 1],
            ['name' => 'Prescription Medications', 'category' => 'health', 'quantity' => 1],
            ['name' => 'Underwear & Socks', 'category' => 'clothing', 'quantity' => $trip->num_days + 2],
            ['name' => 'T-Shirts/Tops', 'category' => 'clothing', 'quantity' => $trip->num_days],
            ['name' => 'Pants/Shorts', 'category' => 'clothing', 'quantity' => max(2, intval($trip->num_days / 2))],
            ['name' => 'Comfortable Walking Shoes', 'category' => 'clothing', 'quantity' => 1],
            ['name' => 'Light Jacket/Sweater', 'category' => 'clothing', 'quantity' => 1],
        ];

        if (in_array($trip->destination->category, ['beach', 'island'])) {
            array_push($defaultItems,
                ['name' => 'Swimsuit', 'category' => 'clothing', 'quantity' => 2],
                ['name' => 'Sunscreen SPF 50+', 'category' => 'health', 'quantity' => 1],
                ['name' => 'Beach Towel', 'category' => 'essentials', 'quantity' => 1],
                ['name' => 'Sunglasses', 'category' => 'essentials', 'quantity' => 1]
            );
        }

        if ($trip->travel_style === 'adventure') {
            array_push($defaultItems,
                ['name' => 'Hiking Boots', 'category' => 'clothing', 'quantity' => 1],
                ['name' => 'Trekking Poles', 'category' => 'essentials', 'quantity' => 2],
                ['name' => 'Waterproof Jacket', 'category' => 'clothing', 'quantity' => 1],
                ['name' => 'Headlamp', 'category' => 'essentials', 'quantity' => 1]
            );
        }

        foreach ($defaultItems as $index => $item) {
            $trip->packingItems()->create(array_merge($item, ['sort_order' => $index]));
        }
    }

    protected function getBudgetFactor(string $style): float
    {
        return match($style) {
            'backpacker' => 0.5,
            'budget'     => 0.8,
            'family'     => 1.2,
            'cultural'   => 1.3,
            'adventure'  => 1.5,
            'luxury'     => 3.0,
            default      => 1.0,
        };
    }

    protected function getMockHighlights(Destination $destination): array
    {
        return [
            ['name' => "Explore {$destination->name} Old Town", 'description' => 'Walk through the historical streets and soak in the local atmosphere.', 'cost' => 0],
            ['name' => 'Main Museum & Gallery', 'description' => 'See the most famous artifacts and art of the region.', 'cost' => 25],
            ['name' => 'Panoramic City Viewpoint', 'description' => "Get a breathtaking panoramic view of {$destination->name}.", 'cost' => 15],
            ['name' => 'Local Market Tour', 'description' => 'Taste local delicacies and shop for unique souvenirs.', 'cost' => 30],
            ['name' => 'Botanical Gardens', 'description' => 'Relax in the beautifully manicured gardens.', 'cost' => 10],
            ['name' => 'Historical Monument', 'description' => 'Visit the iconic landmark that defines the city.', 'cost' => 20],
            ['name' => 'Guided Cultural Tour', 'description' => 'Explore the city with a knowledgeable local guide.', 'cost' => 45],
        ];
    }
}
