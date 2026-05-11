<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTripRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'          => 'required|string|max:255',
            'budget'         => 'nullable|numeric|min:0',
            'currency'       => 'nullable|string|size:3',
            'travel_style'   => 'required|in:luxury,budget,adventure,cultural,backpacker,family',
            'num_travelers'  => 'required|integer|min:1',
            'status'         => 'required|in:planning,confirmed,completed,cancelled',
            'notes'          => 'nullable|string',
            'is_public'      => 'boolean',
            'budget_mode'    => 'nullable|in:standard,budget_friendly',
        ];
    }
}
