<?php

namespace App\Http\Requests\PMS;

use Illuminate\Foundation\Http\FormRequest;

class ProposalUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'budget' => 'required|numeric|min:0',
            'tenure_years' => 'nullable|integer|min:0',
            'tenure_months' => 'nullable|integer|min:0|max:11',
            'tenure_days' => 'nullable|integer|min:0|max:30',
         // 'expected_start_date' => 'required|date|after_or_equal:today',
            'expected_start_date' => 'nullable|date',
            'expected_end_date' => 'nullable|date|after:expected_start_date',
            'estimated_expense' => 'required|numeric|min:0',
            'revenue' => 'required|numeric|min:0',
            'technical_details' => 'nullable|string',
            'methodology' => 'nullable|string',
            'documents' => 'nullable|array',
            'documents.*' => 'file|max:10240', // 10MB max
        //     'expense_components' => 'required|array|min:1',
        // 'expense_components.*.category_id' => 'required|exists:expense_categories,id',
        // 'expense_components.*.component' => 'required|string|max:255',
        // 'expense_components.*.amount' => 'required|numeric|min:0',
         'expense_components' => 'required|array|min:1',
        'expense_components.*.category_id' => 'required|exists:expense_categories,id',
        'expense_components.*.group' => 'nullable|string|max:100',
        'expense_components.*.component' => 'required|string|max:255',
        'expense_components.*.mandays' => 'nullable|numeric|min:0',
        'expense_components.*.rate' => 'nullable|numeric|min:0',
        'expense_components.*.amount' => 'required|numeric|min:0',
        ];
    }
}