<?php

namespace App\Http\Requests\PMS;

use Illuminate\Foundation\Http\FormRequest;

class InvoiceUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'milestone_id' => 'nullable|exists:milestones,id',
            'invoice_date' => 'required|date',
               'invoice_type' => 'required',
            'due_date' => 'required|date|after:invoice_date',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
              'items'                          => 'required|array|min:1',
        'items.*.description'            => 'required|string|max:255',
        'items.*.amount'                 => 'required|numeric|min:0',
        'items.*.tax_percentage'         => 'nullable|numeric|min:0|max:100',
        'items.*.tax_amount'             => 'nullable|numeric|min:0',
        'items.*.total_with_tax'         => 'nullable|numeric|min:0',
        ];
    }
}