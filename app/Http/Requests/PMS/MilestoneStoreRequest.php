<?php

namespace App\Http\Requests\PMS;

use Illuminate\Foundation\Http\FormRequest;

class MilestoneStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            // 'start_date' => 'required|date|after_or_equal:today',
             'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'weightage' => 'required|numeric|min:0|max:100',
            'invoice_trigger' => 'nullable|boolean',
        ];
    }
}