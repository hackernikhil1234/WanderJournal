<?php

namespace App\Services;

use App\Models\Trip;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class WeatherService
{
    protected string $apiKey;
    protected string $baseUrl = 'https://api.openweathermap.org/data/2.5';

    public function __construct()
    {
        $this->apiKey = config('services.openweather.key', 'YOUR_OPENWEATHER_API_KEY');
    }

    public function getForecast(float $lat, float $lon): ?array
    {
        if ($this->apiKey === 'YOUR_OPENWEATHER_API_KEY' || empty($this->apiKey)) {
            return $this->getMockForecast();
        }

        $cacheKey = "weather_forecast_{$lat}_{$lon}";

        return Cache::remember($cacheKey, now()->addHours(3), function () use ($lat, $lon) {
            try {
                $response = Http::get("{$this->baseUrl}/forecast", [
                    'lat' => $lat,
                    'lon' => $lon,
                    'appid' => $this->apiKey,
                    'units' => 'metric',
                ]);

                if ($response->successful()) {
                    return $this->processForecastData($response->json());
                }
                
                Log::warning('OpenWeather API error: ' . $response->body());
                return null;
            } catch (\Exception $e) {
                Log::error('OpenWeather Exception: ' . $e->getMessage());
                return null;
            }
        });
    }

    protected function processForecastData(array $data): array
    {
        $daily = [];
        foreach ($data['list'] as $item) {
            $date = date('Y-m-d', $item['dt']);
            
            // Only take the noon forecast for each day
            if (str_contains($item['dt_txt'], '12:00:00') && count($daily) < 5) {
                $daily[] = [
                    'date' => $date,
                    'temp' => round($item['main']['temp']),
                    'condition' => $item['weather'][0]['main'],
                    'description' => $item['weather'][0]['description'],
                    'icon' => $item['weather'][0]['icon'],
                    'icon_url' => "https://openweathermap.org/img/wn/{$item['weather'][0]['icon']}@2x.png",
                ];
            }
        }
        
        return [
            'city' => $data['city']['name'],
            'daily' => $daily
        ];
    }

    protected function getMockForecast(): array
    {
        $daily = [];
        $conditions = ['Clear', 'Clouds', 'Rain', 'Clear', 'Clouds'];
        $icons = ['01d', '02d', '10d', '01d', '03d'];
        
        for ($i = 0; $i < 5; $i++) {
            $daily[] = [
                'date' => now()->addDays($i)->format('Y-m-d'),
                'temp' => rand(20, 32),
                'condition' => $conditions[$i],
                'description' => strtolower($conditions[$i]),
                'icon' => $icons[$i],
                'icon_url' => "https://openweathermap.org/img/wn/{$icons[$i]}@2x.png",
            ];
        }

        return [
            'city' => 'Mock City',
            'daily' => $daily
        ];
    }
}
