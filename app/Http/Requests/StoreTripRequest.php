<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTripRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'destination_id'             => 'required|exists:destinations,id',
            'title'                      => 'required|string|max:255',
            'dates'                      => 'required|string',
            'budget'                     => 'nullable|numeric|min:0',
            'currency'                   => 'nullable|string|size:3',
            'travel_style'               => 'required|in:luxury,budget,adventure,cultural,backpacker,family',
            'num_travelers'              => 'required|integer|min:1|max:50',
            'interests'                  => 'nullable|array',
            'food_preferences'           => 'nullable|string|max:255',
            'accommodation_type'         => 'nullable|string|max:100',
            'transportation_preference'  => 'nullable|string|max:100',
            'budget_mode'                => 'nullable|in:standard,budget_friendly',
            'notes'                      => 'nullable|string|max:2000',
        ];
    }
}
