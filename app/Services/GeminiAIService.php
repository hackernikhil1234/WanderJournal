<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\Trip;

class GeminiAIService
{
    private string $apiKey;
    private string $model;
    private string $baseUrl = 'https://generativelanguage.googleapis.com/v1';

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key', env('GEMINI_API_KEY', ''));
        $this->model  = config('services.gemini.model', env('GEMINI_MODEL', 'gemini-2.5-flash-lite'));
    }

    /**
     * Check if the AI service is properly configured.
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey) && $this->apiKey !== 'YOUR_GEMINI_API_KEY_FROM_AISTUDIO_GOOGLE_COM';
    }

    /**
     * Generate a complete day-wise itinerary for a trip.
     */
    public function generateItinerary(Trip $trip): ?array
    {
        if (!$this->isConfigured()) return null;

        $destination = $trip->destination;
        $prompt = $this->buildItineraryPrompt($trip);

        $response = $this->callGemini($prompt);
        if (!$response) return null;

        return $this->parseJsonResponse($response);
    }

    /**
     * Generate a smart AI packing list based on trip details.
     */
    public function generatePackingList(Trip $trip): ?array
    {
        if (!$this->isConfigured()) return null;

        $destination = $trip->destination;
        $prompt = <<<PROMPT
You are a smart travel packing assistant. Generate a comprehensive packing list for this trip:

Destination: {$destination->name}, {$destination->country}
Travel Dates: {$trip->start_date->format('M d')} to {$trip->end_date->format('M d, Y')} ({$trip->num_days} days)
Travel Style: {$trip->travel_style}
Destination Category: {$destination->category}
Number of Travelers: {$trip->num_travelers}

Return ONLY valid JSON with this exact structure (no markdown, no explanation):
{
  "categories": [
    {
      "name": "Documents",
      "icon": "fa-file",
      "color": "blue",
      "items": [
        { "name": "Passport", "quantity": 1, "essential": true }
      ]
    }
  ]
}

Include these categories: Documents, Clothing, Electronics, Toiletries, Health & Medical, Essentials.
Add destination-specific items (beach/mountain/city-specific). Mark essential items.
PROMPT;

        $response = $this->callGemini($prompt);
        return $response ? $this->parseJsonResponse($response) : null;
    }

    /**
     * Generate budget breakdown and optimization tips.
     */
    public function generateBudgetBreakdown(Trip $trip): ?array
    {
        if (!$this->isConfigured()) return null;

        $destination = $trip->destination;
        $budget = $trip->budget ?? $destination->avg_daily_budget * $trip->num_days * $trip->num_travelers;
        $mode = $trip->budget_mode ?? 'standard';

        $prompt = <<<PROMPT
You are a travel budget expert. Provide a detailed budget breakdown for this trip:

Destination: {$destination->name}, {$destination->country}
Duration: {$trip->num_days} days
Travelers: {$trip->num_travelers}
Total Budget: {$budget} {$trip->currency}
Travel Style: {$trip->travel_style}
Budget Mode: {$mode}

Return ONLY valid JSON (no markdown):
{
  "daily_budget": 150,
  "total_estimated": 1200,
  "breakdown": {
    "accommodation": { "percentage": 35, "amount": 420, "tip": "Book 2+ weeks ahead for best rates" },
    "food": { "percentage": 25, "amount": 300, "tip": "Mix local eateries with restaurants" },
    "transport": { "percentage": 20, "amount": 240, "tip": "Use public transport where possible" },
    "activities": { "percentage": 15, "amount": 180, "tip": "Book popular attractions in advance" },
    "shopping": { "percentage": 5, "amount": 60, "tip": "Budget for souvenirs and gifts" }
  },
  "savings_tips": ["Tip 1", "Tip 2", "Tip 3"],
  "best_value_options": ["Option 1", "Option 2"],
  "budget_alerts": ["Alert if spending category"],
  "currency_note": "Local currency tips"
}
PROMPT;

        $response = $this->callGemini($prompt);
        return $response ? $this->parseJsonResponse($response) : null;
    }

    /**
     * Generate travel tips and intelligence for a trip.
     */
    public function generateTravelTips(Trip $trip): ?array
    {
        if (!$this->isConfigured()) return null;

        $destination = $trip->destination;
        $prompt = <<<PROMPT
You are an expert travel guide AI. Generate personalized travel tips and intelligence:

Destination: {$destination->name}, {$destination->country}
Travel Dates: {$trip->start_date->format('F Y')}
Travel Style: {$trip->travel_style}
Travelers: {$trip->num_travelers}

Return ONLY valid JSON (no markdown):
{
  "best_time_tips": ["Morning sightseeing tip", "Evening recommendation"],
  "local_customs": ["Custom 1", "Custom 2"],
  "safety_tips": ["Safety tip 1", "Safety tip 2"],
  "food_must_try": ["Dish 1 - description", "Dish 2 - description"],
  "transport_tips": ["Transport tip 1", "Transport tip 2"],
  "money_tips": ["Money tip 1"],
  "language_tips": ["Phrase 1: Translation", "Phrase 2: Translation"],
  "weather_advice": "General weather advice for this time of year",
  "hidden_gems": ["Hidden gem 1", "Hidden gem 2"],
  "emergency_info": {
    "emergency_number": "112",
    "nearest_hospital_tip": "How to find nearest hospital",
    "embassy_tip": "How to contact embassy"
  }
}
PROMPT;

        $response = $this->callGemini($prompt);
        return $response ? $this->parseJsonResponse($response) : null;
    }

    /**
     * Chat with the AI travel assistant.
     */
    public function chat(string $message, array $context = []): string
    {
        if (!$this->isConfigured()) {
            return "The AI assistant isn't configured yet. Please add your Gemini API key to get personalized travel assistance!";
        }

        $contextText = '';
        if (!empty($context)) {
            $contextText = "User context:\n";
            if (isset($context['trip'])) {
                $contextText .= "- Current trip: {$context['trip']['title']} to {$context['trip']['destination']}\n";
                $contextText .= "- Dates: {$context['trip']['dates']}\n";
                $contextText .= "- Style: {$context['trip']['style']}\n";
            }
            if (isset($context['user'])) {
                $contextText .= "- User: {$context['user']['name']}\n";
                $contextText .= "- Travel style: {$context['user']['style']}\n";
            }
        }

        $systemPrompt = <<<PROMPT
You are WanderBot, the AI travel assistant for WanderJournal — a premium vintage travel planning platform. 
You help users plan trips, discover destinations, optimize budgets, and get travel advice.

Personality: Knowledgeable, friendly, enthusiastic about travel. Use occasional travel metaphors.
Response style: Concise but helpful. Use bullet points when listing items. Keep responses under 200 words.
{$contextText}

User message: {$message}
PROMPT;

        $response = $this->callGemini($systemPrompt);
        return $response ?? "I'm having trouble connecting right now. Please try again in a moment!";
    }

    /**
     * Generate hotel recommendations based on trip details.
     */
    public function generateHotelRecommendations(Trip $trip): ?array
    {
        if (!$this->isConfigured()) return null;

        $destination = $trip->destination;
        $budget = $trip->budget ? round($trip->budget / $trip->num_days * 0.35) : null;
        $budgetText = $budget ? "~\${$budget}/night" : "flexible";

        $prompt = <<<PROMPT
Generate hotel/accommodation recommendations for:
Destination: {$destination->name}, {$destination->country}
Travel Style: {$trip->travel_style}
Accommodation Preference: {$trip->accommodation_type}
Budget per night: {$budgetText}
Travelers: {$trip->num_travelers}
Dates: {$trip->start_date->format('M d')} - {$trip->end_date->format('M d, Y')}

Return ONLY valid JSON (no markdown):
{
  "recommendations": [
    {
      "type": "Hotel",
      "name": "Example Grand Hotel",
      "area": "City Center",
      "price_range": "$150-200/night",
      "rating": 4.5,
      "highlights": ["Feature 1", "Feature 2"],
      "best_for": "Luxury travelers",
      "booking_tip": "Book early for best rates"
    }
  ],
  "areas_to_stay": [
    { "name": "Area name", "description": "Why to stay here", "vibe": "Lively/Quiet/Historic" }
  ],
  "booking_tips": ["General tip 1", "General tip 2"]
}
Include 4 recommendations across different budget tiers and types (hotel, hostel, boutique, apartment).
PROMPT;

        $response = $this->callGemini($prompt);
        return $response ? $this->parseJsonResponse($response) : null;
    }

    /**
     * Make an API call to the Gemini endpoint.
     */
    private function callGemini(string $prompt, int $maxTokens = 4096): ?string
    {
        // Try primary model, fall back to alternatives if unavailable
        $modelsToTry = array_unique([
            $this->model,
            'gemini-2.5-flash-lite',
            'gemini-2.5-flash',
            'gemini-2.0-flash',
        ]);

        foreach ($modelsToTry as $model) {
            try {
                $response = Http::timeout(30)
                    ->withHeaders(['Content-Type' => 'application/json'])
                    ->post("{$this->baseUrl}/models/{$model}:generateContent?key={$this->apiKey}", [
                        'contents' => [
                            ['parts' => [['text' => $prompt]]]
                        ],
                        'generationConfig' => [
                            'maxOutputTokens' => $maxTokens,
                            'temperature' => 0.7,
                        ],
                        'safetySettings' => [
                            ['category' => 'HARM_CATEGORY_DANGEROUS_CONTENT', 'threshold' => 'BLOCK_NONE'],
                        ],
                    ]);

                if ($response->successful()) {
                    $data = $response->json();
                    return $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
                }

                $status = $response->status();
                // 503 = model temporarily unavailable, try next
                // 429 = quota exceeded for this model, try next
                if (in_array($status, [429, 503])) {
                    Log::info("Gemini model {$model} unavailable ({$status}), trying fallback");
                    continue;
                }

                // Other errors (4xx) — log and stop
                Log::warning('Gemini API error', ['model' => $model, 'status' => $status, 'body' => $response->body()]);
                return null;

            } catch (\Exception $e) {
                Log::error('Gemini API exception', ['model' => $model, 'error' => $e->getMessage()]);
                continue;
            }
        }

        Log::warning('All Gemini models failed or exhausted');
        return null;
    }

    /**
     * Parse JSON from AI response (handles markdown code blocks).
     */
    private function parseJsonResponse(string $text): ?array
    {
        // Strip markdown code blocks if present
        $text = preg_replace('/^```(?:json)?\s*/m', '', $text);
        $text = preg_replace('/\s*```$/m', '', $text);
        $text = trim($text);

        // Find JSON object/array
        if (preg_match('/(\{.*\}|\[.*\])/s', $text, $matches)) {
            $text = $matches[1];
        }

        $decoded = json_decode($text, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::warning('Failed to parse Gemini JSON response', ['text' => substr($text, 0, 500)]);
            return null;
        }

        return $decoded;
    }

    /**
     * Build the itinerary generation prompt.
     */
    private function buildItineraryPrompt(Trip $trip): string
    {
        $destination = $trip->destination;
        $interests = $trip->interests ?? 'General sightseeing, local culture, food';
        $foodPref = $trip->food_preferences ?? 'Any cuisine';
        $accomm = $trip->accommodation_type ?? 'Hotel';
        $transport = $trip->transportation_preference ?? 'Any';
        $mode = $trip->budget_mode === 'budget_friendly' ? 'Budget-friendly options preferred' : 'Standard options';

        return <<<PROMPT
You are an expert travel planner AI. Generate a detailed day-wise itinerary for this trip:

TRIP DETAILS:
- Destination: {$destination->name}, {$destination->country}
- Start Date: {$trip->start_date->format('l, F j, Y')}
- End Date: {$trip->end_date->format('l, F j, Y')}
- Duration: {$trip->num_days} days
- Travelers: {$trip->num_travelers} people
- Travel Style: {$trip->travel_style}
- Budget: {$trip->budget} {$trip->currency} total
- Budget Mode: {$mode}
- Interests: {$interests}
- Food Preferences: {$foodPref}
- Accommodation: {$accomm}
- Transportation: {$transport}
- Destination Category: {$destination->category}

INSTRUCTIONS:
- Create exactly {$trip->num_days} days
- Day 1: Arrival + orientation activities
- Last day: Morning activities + departure prep
- Include: morning activity, lunch recommendation (with restaurant name), afternoon activity, dinner recommendation, evening suggestion
- Add specific place names, not generic descriptions
- Include estimated cost per activity in USD
- Add practical tips per day

Return ONLY valid JSON in this exact structure (no markdown, no explanation before/after):
{
  "trip_summary": "A 2-line engaging description of this trip",
  "estimated_total_cost": 1200,
  "currency": "USD",
  "days": [
    {
      "day_number": 1,
      "date": "2024-06-01",
      "title": "Arrival & First Impressions",
      "theme": "Orientation",
      "daily_tip": "A practical tip for this day",
      "weather_note": "Expected weather this time of year",
      "items": [
        {
          "time": "14:00",
          "end_time": "15:30",
          "title": "Activity name",
          "description": "2-3 sentence description with what to see/do",
          "location": "Specific place name",
          "type": "attraction",
          "cost_per_person": 25,
          "duration_minutes": 90,
          "booking_required": false,
          "insider_tip": "A useful tip"
        }
      ]
    }
  ],
  "top_tips": ["Tip 1", "Tip 2", "Tip 3"],
  "best_restaurants": [
    { "name": "Restaurant Name", "cuisine": "Italian", "price_range": "$$", "must_try": "Dish name" }
  ],
  "nearby_attractions": [
    { "name": "Place", "distance": "2km", "type": "cultural" }
  ]
}
PROMPT;
    }
}
