<?php

namespace App\Services;

use App\Models\Trip;
use App\Models\ItineraryDay;
use App\Models\ItineraryItem;
use App\Models\Destination;
use Carbon\Carbon;

class ItineraryGeneratorService
{
    /**
     * Generate a complete smart itinerary for a trip
     */
    public function generateForTrip(Trip $trip): void
    {
        // 1. Clear existing itinerary if any
        $trip->itineraryDays()->delete();
        
        // 2. Generate days based on start and end dates
        $startDate = Carbon::parse($trip->start_date);
        $endDate = Carbon::parse($trip->end_date);
        $numDays = $trip->num_days;
        
        $days = [];
        for ($i = 1; $i <= $numDays; $i++) {
            $currentDate = clone $startDate;
            $currentDate->addDays($i - 1);
            
            $days[] = ItineraryDay::create([
                'trip_id' => $trip->id,
                'day_number' => $i,
                'date' => $currentDate,
                'title' => $this->generateDayTitle($i, $numDays),
            ]);
        }
        
        // 3. Generate items based on destination highlights & user interests
        $this->populateDayItems($trip, $days);
        
        // 4. Generate basic packing list
        $this->generatePackingList($trip);
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
            'Hidden Gems',
            'Relaxation & Leisure'
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
            $isArrivalDay = ($index === 0);
            $isDepartureDay = ($index === count($days) - 1);
            
            $sortOrder = 1;
            
            // Morning
            if (!$isArrivalDay) {
                ItineraryItem::create([
                    'itinerary_day_id' => $day->id,
                    'title' => 'Breakfast at local cafe',
                    'start_time' => '08:30',
                    'end_time' => '09:30',
                    'type' => 'restaurant',
                    'cost' => 15 * $budgetFactor,
                    'sort_order' => $sortOrder++,
                ]);
                
                // Morning activity
                $activity = $highlights[$highlightIndex % count($highlights)];
                $highlightIndex++;
                
                ItineraryItem::create([
                    'itinerary_day_id' => $day->id,
                    'title' => $activity['name'],
                    'description' => $activity['description'],
                    'location' => $destination->name,
                    'start_time' => '10:00',
                    'end_time' => '12:30',
                    'type' => 'attraction',
                    'cost' => ($activity['cost'] ?? 25) * $budgetFactor,
                    'sort_order' => $sortOrder++,
                ]);
            } else {
                // Arrival
                ItineraryItem::create([
                    'itinerary_day_id' => $day->id,
                    'title' => 'Arrive and check in to accommodation',
                    'start_time' => '14:00',
                    'end_time' => '15:30',
                    'type' => 'hotel',
                    'cost' => 0,
                    'sort_order' => $sortOrder++,
                ]);
            }
            
            // Lunch
            ItineraryItem::create([
                'itinerary_day_id' => $day->id,
                'title' => 'Lunch',
                'start_time' => '13:00',
                'end_time' => '14:00',
                'type' => 'restaurant',
                'cost' => 25 * $budgetFactor,
                'sort_order' => $sortOrder++,
            ]);
            
            // Afternoon
            if (!$isDepartureDay) {
                $activity = $highlights[$highlightIndex % count($highlights)];
                $highlightIndex++;
                
                ItineraryItem::create([
                    'itinerary_day_id' => $day->id,
                    'title' => $activity['name'],
                    'description' => $activity['description'],
                    'location' => $destination->name,
                    'start_time' => '14:30',
                    'end_time' => '17:00',
                    'type' => 'attraction',
                    'cost' => ($activity['cost'] ?? 20) * $budgetFactor,
                    'sort_order' => $sortOrder++,
                ]);
            } else {
                // Departure
                ItineraryItem::create([
                    'itinerary_day_id' => $day->id,
                    'title' => 'Head to airport/station for departure',
                    'start_time' => '15:00',
                    'end_time' => '16:00',
                    'type' => 'transport',
                    'cost' => 30,
                    'sort_order' => $sortOrder++,
                ]);
            }
            
            // Dinner
            if (!$isDepartureDay) {
                ItineraryItem::create([
                    'itinerary_day_id' => $day->id,
                    'title' => 'Dinner & Evening Walk',
                    'start_time' => '19:00',
                    'end_time' => '21:00',
                    'type' => 'restaurant',
                    'cost' => 45 * $budgetFactor,
                    'sort_order' => $sortOrder++,
                ]);
            }
        }
    }
    
    protected function generatePackingList(Trip $trip): void
    {
        $trip->packingItems()->delete();
        
        $defaultItems = [
            ['name' => 'Passport/ID', 'category' => 'documents', 'quantity' => 1],
            ['name' => 'Tickets & Reservations', 'category' => 'documents', 'quantity' => 1],
            ['name' => 'Travel Insurance', 'category' => 'documents', 'quantity' => 1],
            ['name' => 'Phone & Charger', 'category' => 'electronics', 'quantity' => 1],
            ['name' => 'Universal Adapter', 'category' => 'electronics', 'quantity' => 1],
            ['name' => 'Toothbrush & Paste', 'category' => 'toiletries', 'quantity' => 1],
            ['name' => 'Deodorant', 'category' => 'toiletries', 'quantity' => 1],
            ['name' => 'First Aid Kit', 'category' => 'health', 'quantity' => 1],
            ['name' => 'Prescription Meds', 'category' => 'health', 'quantity' => 1],
            ['name' => 'Underwear & Socks', 'category' => 'clothing', 'quantity' => $trip->num_days + 2],
            ['name' => 'T-Shirts/Tops', 'category' => 'clothing', 'quantity' => $trip->num_days],
            ['name' => 'Pants/Shorts', 'category' => 'clothing', 'quantity' => max(2, intval($trip->num_days/2))],
            ['name' => 'Comfortable Walking Shoes', 'category' => 'clothing', 'quantity' => 1],
            ['name' => 'Light Jacket/Sweater', 'category' => 'clothing', 'quantity' => 1],
        ];
        
        if ($trip->destination->category === 'beach' || $trip->destination->category === 'island') {
            $defaultItems[] = ['name' => 'Swimsuit', 'category' => 'clothing', 'quantity' => 2];
            $defaultItems[] = ['name' => 'Sunscreen', 'category' => 'health', 'quantity' => 1];
            $defaultItems[] = ['name' => 'Beach Towel', 'category' => 'essentials', 'quantity' => 1];
            $defaultItems[] = ['name' => 'Sunglasses', 'category' => 'essentials', 'quantity' => 1];
        }
        
        foreach ($defaultItems as $index => $item) {
            $trip->packingItems()->create(array_merge($item, ['sort_order' => $index]));
        }
    }
    
    protected function getBudgetFactor(string $style): float
    {
        return match($style) {
            'backpacker' => 0.5,
            'budget' => 0.8,
            'family' => 1.2,
            'cultural' => 1.3,
            'adventure' => 1.5,
            'luxury' => 3.0,
            default => 1.0,
        };
    }
    
    protected function getMockHighlights(Destination $destination): array
    {
        return [
            ['name' => "Explore {$destination->name} Old Town", 'description' => "Walk through the historical streets and soak in the atmosphere.", 'cost' => 0],
            ['name' => "Main Museum/Gallery", 'description' => "See the most famous artifacts and art of the region.", 'cost' => 25],
            ['name' => "City Viewpoint", 'description' => "Get a panoramic view of {$destination->name}.", 'cost' => 15],
            ['name' => "Local Market Tour", 'description' => "Taste local delicacies and shop for souvenirs.", 'cost' => 30],
            ['name' => "Botanical Gardens", 'description' => "Relax in the beautiful manicured nature.", 'cost' => 10],
            ['name' => "Historical Monument", 'description' => "Visit the iconic landmark that defines the city.", 'cost' => 20],
        ];
    }
}
