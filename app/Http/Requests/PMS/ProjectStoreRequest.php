<?php

namespace App\Http\Requests\PMS;

use Illuminate\Foundation\Http\FormRequest;

class ProjectStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            // Base Project Info
            'title' => 'required|string|max:255',
            'project_investigator_id' => 'required|exists:users,id',
            'pi_expected_time' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'budget' => 'required|numeric|min:0', // Overall Project Budget
            'revenue' => 'required|numeric', // Allow negative revenue (loss)
            'description' => 'nullable|string',
            'technical_details' => 'nullable|string',
            'methodology' => 'nullable|string',

            // Team Members (Optional)
            'team_members_json' => 'nullable|sometimes|json',

            // Yearly Estimates Structure (The Core of the New Logic)
            'yearly_estimates' => 'required|array',
            'yearly_estimates.*.financial_year_id' => 'required|exists:financial_years,id',
            'yearly_estimates.*.amount' => 'required|numeric|min:0', // Yearly Budget Amount
            'yearly_estimates.*.notes' => 'nullable|string',

            // Yearly Estimated Components
            'yearly_estimates.*.components' => 'nullable|array',
            'yearly_estimates.*.components.*.group' => 'required|string', // HR, Travel, etc.
            'yearly_estimates.*.components.*.component' => 'required|string',
            'yearly_estimates.*.components.*.category_id' => 'nullable|exists:expense_categories,id',
            'yearly_estimates.*.components.*.mandays' => 'nullable|numeric|min:0',
            'yearly_estimates.*.components.*.rate' => 'nullable|numeric|min:0',
            'yearly_estimates.*.components.*.amount' => 'required|numeric|min:0',

            // Budgeted Components (Overall) - Optional/Nullable
            'budgeted_components' => 'nullable|array',
            'budgeted_components.*.group' => 'required|string',
            'budgeted_components.*.component' => 'required|string',
            'budgeted_components.*.category_id' => 'nullable|exists:expense_categories,id',
            'budgeted_components.*.mandays' => 'nullable|numeric|min:0',
            'budgeted_components.*.rate' => 'nullable|numeric|min:0',
            'budgeted_components.*.amount' => 'required|numeric|min:0',

            // Documents
            'documents' => 'nullable|array',
            'documents.*' => 'file|max:10240', // 10MB max

            // Flags
            'copy_estimated_expenses' => 'sometimes|boolean',
            'copy_budgeted_expenses' => 'sometimes|boolean',
        ];
    }
}