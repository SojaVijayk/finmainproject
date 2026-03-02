<?php

namespace App\Http\Requests\PMS;

use Illuminate\Foundation\Http\FormRequest;

class ProjectUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'budget' => 'required|numeric|min:0',
            'estimated_expense' => 'required|numeric|min:0',
            'revenue' => 'required|numeric|min:0',
            'project_investigator_id' => 'required|exists:users,id',
            'team_members' => 'nullable|array',
            'team_members.*' => 'exists:users,id',
        //       'expense_components' => 'required|array|min:1',
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
