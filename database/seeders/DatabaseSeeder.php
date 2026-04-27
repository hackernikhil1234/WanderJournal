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
                'name' => 'Kyoto',
                'country' => 'Japan',
                'continent' => 'Asia',
                'category' => 'cultural',
                'description' => 'Famous for its classical Buddhist temples, as well as gardens, imperial palaces, Shinto shrines and traditional wooden houses.',
                'short_description' => 'The heart of traditional Japan with thousands of temples.',
                'cover_image' => 'https://images.unsplash.com/photo-1493976040374-85c8e12f0c0e?w=800&auto=format&fit=crop',
                'best_time_to_visit' => 'March to May (Spring) or Sept to Nov (Autumn)',
                'avg_daily_budget' => 120,
                'currency' => 'JPY',
                'latitude' => 35.0116,
                'longitude' => 135.7681,
                'featured' => true,
                'popularity_score' => 95,
                'highlights' => [
                    ['name' => 'Fushimi Inari Shrine', 'description' => 'Famous shrine with thousands of vermilion torii gates.', 'cost' => 0],
                    ['name' => 'Kinkaku-ji (Golden Pavilion)', 'description' => 'Zen Buddhist temple covered in gold leaf.', 'cost' => 5],
                    ['name' => 'Arashiyama Bamboo Grove', 'description' => 'Walking path through a towering bamboo forest.', 'cost' => 0],
                ]
            ],
            [
                'name' => 'Santorini',
                'country' => 'Greece',
                'continent' => 'Europe',
                'category' => 'island',
                'description' => 'A volcanic island in the Cyclades group of the Greek islands. It is located between Ios and Anafi islands. It is famous for dramatic views, stunning sunsets from Oia town, the strange white aubergine, the town of Thira and naturally its very own active volcano.',
                'short_description' => 'Iconic white and blue architecture overlooking the Aegean Sea.',
                'cover_image' => 'https://images.unsplash.com/photo-1613395877344-13d4a8e0d49e?w=800&auto=format&fit=crop',
                'best_time_to_visit' => 'Sept to Oct',
                'avg_daily_budget' => 180,
                'currency' => 'EUR',
                'latitude' => 36.3932,
                'longitude' => 25.4615,
                'featured' => true,
                'popularity_score' => 90,
                'highlights' => [
                    ['name' => 'Oia Sunset', 'description' => 'Watch the world-famous sunset over the caldera.', 'cost' => 0],
                    ['name' => 'Red Beach', 'description' => 'Unique beach with red volcanic cliffs.', 'cost' => 0],
                    ['name' => 'Akrotiri Archaeological Site', 'description' => 'Ancient Minoan bronze age settlement.', 'cost' => 12],
                ]
            ],
            [
                'name' => 'Banff National Park',
                'country' => 'Canada',
                'continent' => 'North America',
                'category' => 'mountains',
                'description' => 'Banff National Park is Canada\'s oldest national park. It encompasses mountainous terrain, with numerous glaciers and ice fields, dense coniferous forest, and alpine landscapes.',
                'short_description' => 'Turquoise glacial lakes and towering Rocky Mountain peaks.',
                'cover_image' => 'https://images.unsplash.com/photo-1544365558-35aa4afcf11f?w=800&auto=format&fit=crop',
                'best_time_to_visit' => 'June to August or December to March (for skiing)',
                'avg_daily_budget' => 150,
                'currency' => 'CAD',
                'latitude' => 51.4968,
                'longitude' => -115.9281,
                'featured' => true,
                'popularity_score' => 88,
                'highlights' => [
                    ['name' => 'Lake Louise', 'description' => 'Iconic turquoise lake surrounded by mountains.', 'cost' => 0],
                    ['name' => 'Banff Gondola', 'description' => 'Ride to the top of Sulphur Mountain.', 'cost' => 60],
                    ['name' => 'Johnston Canyon', 'description' => 'Scenic hike with waterfalls.', 'cost' => 0],
                ]
            ],
            [
                'name' => 'Machu Picchu',
                'country' => 'Peru',
                'continent' => 'South America',
                'category' => 'historical',
                'description' => 'An Incan citadel set high in the Andes Mountains in Peru, above the Urubamba River valley. Built in the 15th century and later abandoned.',
                'short_description' => 'Ancient Incan citadel perched high in the Andes.',
                'cover_image' => 'https://images.unsplash.com/photo-1587595431973-160d0d94add1?w=800&auto=format&fit=crop',
                'best_time_to_visit' => 'April to October',
                'avg_daily_budget' => 90,
                'currency' => 'PEN',
                'latitude' => -13.1631,
                'longitude' => -72.5450,
                'featured' => false,
                'popularity_score' => 92,
                'highlights' => [
                    ['name' => 'Inca Trail', 'description' => 'Classic multi-day trek to the citadel.', 'cost' => 600],
                    ['name' => 'Huayna Picchu', 'description' => 'Steep climb for a birds-eye view of the ruins.', 'cost' => 70],
                    ['name' => 'Sun Gate', 'description' => 'The original entrance to Machu Picchu.', 'cost' => 0],
                ]
            ],
            [
                'name' => 'Marrakech',
                'country' => 'Morocco',
                'continent' => 'Africa',
                'category' => 'city',
                'description' => 'A former imperial city in western Morocco, is a major economic center and home to mosques, palaces and gardens. The medina is a densely packed, walled medieval city.',
                'short_description' => 'Vibrant markets, stunning palaces, and maze-like alleys.',
                'cover_image' => 'https://images.unsplash.com/photo-1539020140153-e479b8c22e70?w=800&auto=format&fit=crop',
                'best_time_to_visit' => 'March to May and Sept to Nov',
                'avg_daily_budget' => 60,
                'currency' => 'MAD',
                'latitude' => 31.6295,
                'longitude' => -7.9811,
                'featured' => false,
                'popularity_score' => 85,
                'highlights' => [
                    ['name' => 'Jemaa el-Fnaa', 'description' => 'Main square with food stalls and entertainers.', 'cost' => 0],
                    ['name' => 'Jardin Majorelle', 'description' => 'Beautiful botanical garden created by Jacques Majorelle.', 'cost' => 15],
                    ['name' => 'Bahia Palace', 'description' => '19th-century palace with stunning tilework.', 'cost' => 7],
                ]
            ],
            [
                'name' => 'Bali',
                'country' => 'Indonesia',
                'continent' => 'Asia',
                'category' => 'island',
                'description' => 'An Indonesian island known for its forested volcanic mountains, iconic rice paddies, beaches and coral reefs. The island is home to religious sites such as cliffside Uluwatu Temple.',
                'short_description' => 'Tropical paradise of rice terraces and surf beaches.',
                'cover_image' => 'https://images.unsplash.com/photo-1537996194471-e657df975ab4?w=800&auto=format&fit=crop',
                'best_time_to_visit' => 'April to October',
                'avg_daily_budget' => 70,
                'currency' => 'IDR',
                'latitude' => -8.4095,
                'longitude' => 115.1889,
                'featured' => true,
                'popularity_score' => 94,
                'highlights' => [
                    ['name' => 'Ubud Monkey Forest', 'description' => 'Nature reserve and temple complex.', 'cost' => 5],
                    ['name' => 'Tegallalang Rice Terrace', 'description' => 'Beautiful terraced rice fields.', 'cost' => 2],
                    ['name' => 'Uluwatu Temple', 'description' => 'Cliffside temple with sunset ocean views.', 'cost' => 4],
                ]
            ]
        ];

        foreach ($destinations as $destData) {
            $destData['slug'] = Str::slug($destData['name']);
            $destData['tags'] = ['must_visit', strtolower($destData['category']), strtolower($destData['continent'])];
            $dest = Destination::create($destData);
            
            // Add some mock reviews
            for ($i = 0; $i < rand(3, 8); $i++) {
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
