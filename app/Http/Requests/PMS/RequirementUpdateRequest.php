<?php

namespace App\Http\Requests\PMS;

use Illuminate\Foundation\Http\FormRequest;

class RequirementUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'type_id' => 'required|in:1,2,3',
            'project_category_id' => 'required|exists:project_categories,id',
            'project_subcategory_id' => 'nullable|exists:project_subcategories,id',
            'client_id' => 'required|exists:clients,id',
            'client_contact_person_id' => 'required|exists:client_contact_people,id',
            'ref_no' => 'nullable|string|max:255',
            'documents' => 'nullable|array',
            'documents.*' => 'file|max:10240', // 10MB max
        ];
    }
}
