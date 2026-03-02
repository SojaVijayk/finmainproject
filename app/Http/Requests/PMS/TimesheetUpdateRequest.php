<?php

namespace App\Http\Requests\PMS;

use Illuminate\Foundation\Http\FormRequest;

class TimesheetUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'date' => 'required|date',
            'category_id' => 'required|exists:timesheet_categories,id',
            'project_id' => 'nullable|exists:projects,id',
            'hours' => 'required|numeric|min:0.1|max:24',
            'description' => 'nullable|string|max:1000',
        ];
    }

    public function prepareForValidation()
    {
        if ($this->has('project_id') && $this->project_id == 'null') {
            $this->merge(['project_id' => null]);
        }
    }
}