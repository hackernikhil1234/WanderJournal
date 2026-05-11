<?php

namespace App\Jobs;

use App\Models\Trip;
use App\Services\ItineraryGeneratorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateAiItineraryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 120; // 2 minutes max
    public $tries = 2;

    public function __construct(
        public Trip $trip
    ) {}

    public function handle(ItineraryGeneratorService $generator): void
    {
        Log::info("Starting background AI generation for trip ID: {$this->trip->id}");
        
        try {
            $generator->generateForTrip($this->trip);
            
            // Unset the generating flag upon success
            $meta = $this->trip->ai_metadata;
            if (is_string($meta)) {
                $meta = json_decode($meta, true) ?? [];
            } elseif (!is_array($meta)) {
                $meta = [];
            }
            
            unset($meta['is_generating']);
            $this->trip->ai_metadata = $meta;
            $this->trip->save();
            
            Log::info("Successfully generated itinerary for trip ID: {$this->trip->id}");
        } catch (\Exception $e) {
            Log::error("Failed to generate AI itinerary for trip ID: {$this->trip->id}. Error: " . $e->getMessage());
            
            // Remove the generating flag so it doesn't get stuck forever
            $meta = $this->trip->ai_metadata;
            if (is_string($meta)) {
                $meta = json_decode($meta, true) ?? [];
            } elseif (!is_array($meta)) {
                $meta = [];
            }
            
            unset($meta['is_generating']);
            $meta['generation_failed'] = true;
            $this->trip->ai_metadata = $meta;
            $this->trip->save();
            
            throw $e;
        }
    }
}
