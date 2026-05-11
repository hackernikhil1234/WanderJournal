<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'        => 'required|string|max:255',
            'category'     => 'required|in:accommodation,food,transport,activities,shopping,health,communication,other',
            'amount'       => 'required|numeric|min:0',
            'currency'     => 'required|string|size:3',
            'expense_date' => 'required|date',
            'notes'        => 'nullable|string|max:500',
            'is_shared'    => 'boolean',
        ];
    }
}
