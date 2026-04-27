<?php

namespace App\Http\Controllers;

use App\Services\WeatherService;
use Illuminate\Http\Request;

class WeatherController extends Controller
{
    protected $weatherService;

    public function __construct(WeatherService $weatherService)
    {
        $this->weatherService = $weatherService;
    }

    public function getForecast(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric',
            'lon' => 'required|numeric',
        ]);

        $data = $this->weatherService->getForecast($request->lat, $request->lon);

        if (!$data) {
            return response()->json(['error' => 'Unable to fetch weather data'], 500);
        }

        return response()->json($data);
    }
}
