<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Destination;
use App\Models\Review;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Demo User
        $user = User::factory()->create([
            'name' => 'Wanderer',
            'email' => 'demo@wanderjournal.com',
            'password' => Hash::make('password'),
            'travel_style' => 'adventure',
            'interests' => 'nature,photography,food',
            'default_budget' => 2000,
        ]);

        // 2. Create Destinations
        $destinations = [
            [
                'name' => 'Kyoto', 'country' => 'Japan', 'continent' => 'Asia', 'category' => 'cultural',
                'description' => 'Famous for its classical Buddhist temples, as well as gardens, imperial palaces, Shinto shrines and traditional wooden houses.',
                'short_description' => 'The heart of traditional Japan with thousands of temples.',
                'cover_image' => 'https://images.unsplash.com/photo-1493976040374-85c8e12f0c0e?w=800&auto=format&fit=crop',
                'best_time_to_visit' => 'March to May (Spring) or Sept to Nov (Autumn)',
                'avg_daily_budget' => 120, 'currency' => 'JPY', 'latitude' => 35.0116, 'longitude' => 135.7681, 'featured' => true, 'popularity_score' => 95,
                'highlights' => [
                    ['name' => 'Fushimi Inari Shrine', 'description' => 'Famous shrine with thousands of vermilion torii gates.', 'cost' => 0],
                    ['name' => 'Kinkaku-ji', 'description' => 'Zen Buddhist temple covered in gold leaf.', 'cost' => 5],
                ]
            ],
            [
                'name' => 'Santorini', 'country' => 'Greece', 'continent' => 'Europe', 'category' => 'island',
                'description' => 'A volcanic island in the Cyclades group of the Greek islands, famous for dramatic views and stunning sunsets.',
                'short_description' => 'Iconic white and blue architecture overlooking the Aegean Sea.',
                'cover_image' => 'https://images.unsplash.com/photo-1613395877344-13d4a8e0d49e?w=800&auto=format&fit=crop',
                'best_time_to_visit' => 'Sept to Oct',
                'avg_daily_budget' => 180, 'currency' => 'EUR', 'latitude' => 36.3932, 'longitude' => 25.4615, 'featured' => true, 'popularity_score' => 90,
                'highlights' => [
                    ['name' => 'Oia Sunset', 'description' => 'Watch the world-famous sunset over the caldera.', 'cost' => 0],
                ]
            ],
            [
                'name' => 'Banff National Park', 'country' => 'Canada', 'continent' => 'North America', 'category' => 'mountains',
                'description' => 'Banff National Park encompasses mountainous terrain, with numerous glaciers and ice fields, dense coniferous forest, and alpine landscapes.',
                'short_description' => 'Turquoise glacial lakes and towering Rocky Mountain peaks.',
                'cover_image' => 'https://images.unsplash.com/photo-1544365558-35aa4afcf11f?w=800&auto=format&fit=crop',
                'best_time_to_visit' => 'June to August or December to March (for skiing)',
                'avg_daily_budget' => 150, 'currency' => 'CAD', 'latitude' => 51.4968, 'longitude' => -115.9281, 'featured' => true, 'popularity_score' => 88,
                'highlights' => [
                    ['name' => 'Lake Louise', 'description' => 'Iconic turquoise lake surrounded by mountains.', 'cost' => 0],
                ]
            ],
            [
                'name' => 'Machu Picchu', 'country' => 'Peru', 'continent' => 'South America', 'category' => 'historical',
                'description' => 'An Incan citadel set high in the Andes Mountains in Peru, above the Urubamba River valley.',
                'short_description' => 'Ancient Incan citadel perched high in the Andes.',
                'cover_image' => 'https://images.unsplash.com/photo-1587595431973-160d0d94add1?w=800&auto=format&fit=crop',
                'best_time_to_visit' => 'April to October',
                'avg_daily_budget' => 90, 'currency' => 'PEN', 'latitude' => -13.1631, 'longitude' => -72.5450, 'featured' => false, 'popularity_score' => 92,
                'highlights' => [
                    ['name' => 'Inca Trail', 'description' => 'Classic multi-day trek to the citadel.', 'cost' => 600],
                ]
            ],
            [
                'name' => 'Marrakech', 'country' => 'Morocco', 'continent' => 'Africa', 'category' => 'city',
                'description' => 'A former imperial city in western Morocco, home to mosques, palaces and gardens.',
                'short_description' => 'Vibrant markets, stunning palaces, and maze-like alleys.',
                'cover_image' => 'https://images.unsplash.com/photo-1539020140153-e479b8c22e70?w=800&auto=format&fit=crop',
                'best_time_to_visit' => 'March to May and Sept to Nov',
                'avg_daily_budget' => 60, 'currency' => 'MAD', 'latitude' => 31.6295, 'longitude' => -7.9811, 'featured' => false, 'popularity_score' => 85,
                'highlights' => [
                    ['name' => 'Jemaa el-Fnaa', 'description' => 'Main square with food stalls and entertainers.', 'cost' => 0],
                ]
            ],
            [
                'name' => 'Bali', 'country' => 'Indonesia', 'continent' => 'Asia', 'category' => 'island',
                'description' => 'An Indonesian island known for its forested volcanic mountains, iconic rice paddies, beaches and coral reefs.',
                'short_description' => 'Tropical paradise of rice terraces and surf beaches.',
                'cover_image' => 'https://images.unsplash.com/photo-1537996194471-e657df975ab4?w=800&auto=format&fit=crop',
                'best_time_to_visit' => 'April to October',
                'avg_daily_budget' => 70, 'currency' => 'IDR', 'latitude' => -8.4095, 'longitude' => 115.1889, 'featured' => true, 'popularity_score' => 94,
                'highlights' => [
                    ['name' => 'Ubud Monkey Forest', 'description' => 'Nature reserve and temple complex.', 'cost' => 5],
                ]
            ],
            // ADDED GLOBAL DESTINATIONS
            [
                'name' => 'Paris', 'country' => 'France', 'continent' => 'Europe', 'category' => 'city',
                'description' => 'The capital of France, renowned for its art, fashion, gastronomy, and culture.',
                'short_description' => 'The City of Light, famous for the Eiffel Tower and art museums.',
                'cover_image' => 'https://images.unsplash.com/photo-1502602898657-3e91760cbb34?w=800&auto=format&fit=crop',
                'best_time_to_visit' => 'April to June or October to early November',
                'avg_daily_budget' => 200, 'currency' => 'EUR', 'latitude' => 48.8566, 'longitude' => 2.3522, 'featured' => true, 'popularity_score' => 98,
                'highlights' => [
                    ['name' => 'Eiffel Tower', 'description' => 'Iconic iron lattice tower.', 'cost' => 30],
                ]
            ],
            [
                'name' => 'Rome', 'country' => 'Italy', 'continent' => 'Europe', 'category' => 'historical',
                'description' => 'Italy\'s capital, a sprawling, cosmopolitan city with nearly 3,000 years of globally influential art, architecture and culture.',
                'short_description' => 'Ancient ruins and stunning architecture in the Eternal City.',
                'cover_image' => 'https://images.unsplash.com/photo-1552832230-c0197dd311b5?w=800&auto=format&fit=crop',
                'best_time_to_visit' => 'September to November and April to May',
                'avg_daily_budget' => 150, 'currency' => 'EUR', 'latitude' => 41.9028, 'longitude' => 12.4964, 'featured' => true, 'popularity_score' => 97,
                'highlights' => [
                    ['name' => 'Colosseum', 'description' => 'Ancient gladiatorial arena.', 'cost' => 18],
                ]
            ],
            [
                'name' => 'New York City', 'country' => 'USA', 'continent' => 'North America', 'category' => 'city',
                'description' => 'A major commercial, cultural, and financial center in the United States, famous for its skyscrapers and Broadway shows.',
                'short_description' => 'The city that never sleeps, with iconic skyline views.',
                'cover_image' => 'https://images.unsplash.com/photo-1496442226666-8d4d0e62e6e9?w=800&auto=format&fit=crop',
                'best_time_to_visit' => 'April to June and September to early November',
                'avg_daily_budget' => 250, 'currency' => 'USD', 'latitude' => 40.7128, 'longitude' => -74.0060, 'featured' => true, 'popularity_score' => 96,
                'highlights' => [
                    ['name' => 'Statue of Liberty', 'description' => 'Colossal copper statue on Liberty Island.', 'cost' => 25],
                ]
            ],
            [
                'name' => 'London', 'country' => 'United Kingdom', 'continent' => 'Europe', 'category' => 'city',
                'description' => 'The capital of England and the United Kingdom, a 21st-century city with history stretching back to Roman times.',
                'short_description' => 'Historic landmarks seamlessly mixed with modern attractions.',
                'cover_image' => 'https://images.unsplash.com/photo-1513635269975-59663e0ac1ad?w=800&auto=format&fit=crop',
                'best_time_to_visit' => 'May to August',
                'avg_daily_budget' => 180, 'currency' => 'GBP', 'latitude' => 51.5074, 'longitude' => -0.1278, 'featured' => false, 'popularity_score' => 93,
                'highlights' => [
                    ['name' => 'Tower of London', 'description' => 'Historic castle on the River Thames.', 'cost' => 35],
                ]
            ],
            [
                'name' => 'Sydney', 'country' => 'Australia', 'continent' => 'Oceania', 'category' => 'city',
                'description' => 'Capital of New South Wales and one of Australia\'s largest cities, best known for its harborfront Sydney Opera House.',
                'short_description' => 'Stunning harbor, iconic Opera House, and beautiful beaches.',
                'cover_image' => 'https://images.unsplash.com/photo-1506973035872-a4ec16b8e8d9?w=800&auto=format&fit=crop',
                'best_time_to_visit' => 'September to November and March to May',
                'avg_daily_budget' => 160, 'currency' => 'AUD', 'latitude' => -33.8688, 'longitude' => 151.2093, 'featured' => true, 'popularity_score' => 89,
                'highlights' => [
                    ['name' => 'Sydney Opera House', 'description' => 'Multi-venue performing arts centre.', 'cost' => 40],
                ]
            ],
            [
                'name' => 'Cape Town', 'country' => 'South Africa', 'continent' => 'Africa', 'category' => 'city',
                'description' => 'A port city on South Africa’s southwest coast, on a peninsula beneath the imposing Table Mountain.',
                'short_description' => 'Breathtaking coastal city framed by Table Mountain.',
                'cover_image' => 'https://images.unsplash.com/photo-1580060839134-75a5edca2e99?w=800&auto=format&fit=crop',
                'best_time_to_visit' => 'March to May and September to November',
                'avg_daily_budget' => 100, 'currency' => 'ZAR', 'latitude' => -33.9249, 'longitude' => 18.4241, 'featured' => false, 'popularity_score' => 87,
                'highlights' => [
                    ['name' => 'Table Mountain', 'description' => 'Flat-topped mountain overlooking the city.', 'cost' => 25],
                ]
            ],
            [
                'name' => 'Rio de Janeiro', 'country' => 'Brazil', 'continent' => 'South America', 'category' => 'city',
                'description' => 'A huge seaside city in Brazil, famed for its Copacabana and Ipanema beaches, and the Christ the Redeemer statue.',
                'short_description' => 'Vibrant beaches, Carnaval, and the Christ the Redeemer statue.',
                'cover_image' => 'https://images.unsplash.com/photo-1483729558449-99ef09a8c325?w=800&auto=format&fit=crop',
                'best_time_to_visit' => 'December to March',
                'avg_daily_budget' => 80, 'currency' => 'BRL', 'latitude' => -22.9068, 'longitude' => -43.1729, 'featured' => false, 'popularity_score' => 86,
                'highlights' => [
                    ['name' => 'Christ the Redeemer', 'description' => 'Colossal Art Deco statue of Jesus Christ.', 'cost' => 20],
                ]
            ],
            [
                'name' => 'Dubai', 'country' => 'UAE', 'continent' => 'Asia', 'category' => 'city',
                'description' => 'A city and emirate in the United Arab Emirates known for luxury shopping, ultramodern architecture and a lively nightlife scene.',
                'short_description' => 'Ultramodern metropolis rising from the desert sands.',
                'cover_image' => 'https://images.unsplash.com/photo-1512453979798-5ea266f8880c?w=800&auto=format&fit=crop',
                'best_time_to_visit' => 'November to April',
                'avg_daily_budget' => 250, 'currency' => 'AED', 'latitude' => 25.2048, 'longitude' => 55.2708, 'featured' => false, 'popularity_score' => 91,
                'highlights' => [
                    ['name' => 'Burj Khalifa', 'description' => 'The tallest building in the world.', 'cost' => 45],
                ]
            ],
            [
                'name' => 'Singapore', 'country' => 'Singapore', 'continent' => 'Asia', 'category' => 'city',
                'description' => 'A sovereign island city-state in maritime Southeast Asia, famous for its clean streets and modern skyline.',
                'short_description' => 'Futuristic gardens and diverse culinary scenes.',
                'cover_image' => 'https://images.unsplash.com/photo-1525625293386-3f8f99389edd?w=800&auto=format&fit=crop',
                'best_time_to_visit' => 'February to April',
                'avg_daily_budget' => 170, 'currency' => 'SGD', 'latitude' => 1.3521, 'longitude' => 103.8198, 'featured' => false, 'popularity_score' => 88,
                'highlights' => [
                    ['name' => 'Gardens by the Bay', 'description' => 'Nature park with giant futuristic tree structures.', 'cost' => 20],
                ]
            ],
            [
                'name' => 'Istanbul', 'country' => 'Turkey', 'continent' => 'Europe/Asia', 'category' => 'cultural',
                'description' => 'A major city in Turkey that straddles Europe and Asia across the Bosphorus Strait.',
                'short_description' => 'Where East meets West in spectacular architecture.',
                'cover_image' => 'https://images.unsplash.com/photo-1541432901042-2d8bd64b4a9b?w=800&auto=format&fit=crop',
                'best_time_to_visit' => 'April to May and September to October',
                'avg_daily_budget' => 70, 'currency' => 'TRY', 'latitude' => 41.0082, 'longitude' => 28.9784, 'featured' => true, 'popularity_score' => 92,
                'highlights' => [
                    ['name' => 'Hagia Sophia', 'description' => 'Late antique place of worship in Istanbul.', 'cost' => 25],
                ]
            ],
            [
                'name' => 'Bangkok', 'country' => 'Thailand', 'continent' => 'Asia', 'category' => 'city',
                'description' => 'Thailand’s capital, known for ornate shrines and vibrant street life.',
                'short_description' => 'Bustling capital known for street food and ornate temples.',
                'cover_image' => 'https://images.unsplash.com/photo-1508009603885-50cf7cbf1400?w=800&auto=format&fit=crop',
                'best_time_to_visit' => 'November to February',
                'avg_daily_budget' => 50, 'currency' => 'THB', 'latitude' => 13.7563, 'longitude' => 100.5018, 'featured' => false, 'popularity_score' => 90,
                'highlights' => [
                    ['name' => 'Grand Palace', 'description' => 'Complex of buildings at the heart of Bangkok.', 'cost' => 15],
                ]
            ],
            [
                'name' => 'Cairo', 'country' => 'Egypt', 'continent' => 'Africa', 'category' => 'historical',
                'description' => 'Egypt’s sprawling capital, set on the Nile River.',
                'short_description' => 'Gateway to the Pyramids and the Sphinx.',
                'cover_image' => 'https://images.unsplash.com/photo-1539650116574-8efeb43e2b50?w=800&auto=format&fit=crop',
                'best_time_to_visit' => 'October to April',
                'avg_daily_budget' => 40, 'currency' => 'EGP', 'latitude' => 30.0444, 'longitude' => 31.2357, 'featured' => true, 'popularity_score' => 84,
                'highlights' => [
                    ['name' => 'Giza Pyramids', 'description' => 'Ancient pyramid complex.', 'cost' => 20],
                ]
            ],
            [
                'name' => 'Amsterdam', 'country' => 'Netherlands', 'continent' => 'Europe', 'category' => 'city',
                'description' => 'The Netherlands’ capital, known for its artistic heritage, elaborate canal system and narrow houses with gabled facades.',
                'short_description' => 'Scenic canals, historic museums, and bicycle culture.',
                'cover_image' => 'https://images.unsplash.com/photo-1517736996303-4eec4a66bb17?w=800&auto=format&fit=crop',
                'best_time_to_visit' => 'April to May or September to November',
                'avg_daily_budget' => 160, 'currency' => 'EUR', 'latitude' => 52.3676, 'longitude' => 4.9041, 'featured' => false, 'popularity_score' => 91,
                'highlights' => [
                    ['name' => 'Rijksmuseum', 'description' => 'Dutch national museum dedicated to arts and history.', 'cost' => 22],
                ]
            ]
        ];

        foreach ($destinations as $destData) {
            $destData['slug'] = Str::slug($destData['name']);
            $destData['tags'] = ['must_visit', strtolower($destData['category']), strtolower(explode('/', $destData['continent'])[0])];
            $dest = Destination::create($destData);
            
            // Add some mock reviews
            for ($i = 0; $i < rand(2, 5); $i++) {
                Review::create([
                    'user_id' => $user->id,
                    'destination_id' => $dest->id,
                    'rating' => rand(4, 5),
                    'title' => 'Amazing experience!',
                    'body' => 'I absolutely loved this place. The culture, the food, everything was perfect. Highly recommended for ' . $dest->category . ' lovers.',
                    'travel_type' => ['solo', 'couple', 'family'][rand(0, 2)],
                    'visited_month' => 'May 2023',
                    'is_approved' => true
                ]);
            }
        }
    }
}
