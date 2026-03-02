<?php

namespace App\Exports;

use App\Models\PMS\Expense;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExpensesExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $query = Expense::with(['project', 'category', 'vendor', 'creator']);

        if ($this->request->has('project_id') && $this->request->project_id) {
            $query->where('project_id', $this->request->project_id);
        }

        if ($this->request->has('category_id') && $this->request->category_id) {
            $query->where('category_id', $this->request->category_id);
        }

        if ($this->request->has('vendor_id') && $this->request->vendor_id) {
            $query->where('vendor_id', $this->request->vendor_id);
        }

        if ($this->request->has('payment_mode') && $this->request->payment_mode) {
            $query->where('payment_mode', $this->request->payment_mode);
        }

        if ($this->request->has('start_date') && $this->request->start_date) {
            $query->where('payment_date', '>=', $this->request->start_date);
        }

        if ($this->request->has('end_date') && $this->request->end_date) {
            $query->where('payment_date', '<=', $this->request->end_date);
        }

        return $query->orderBy('payment_date', 'desc');
    }

    public function headings(): array
    {
        return [
            'ID',
            'Project',
            'Category',
            'Vendor',
            'Amount',
            'Tax',
            'Total Amount',
            'Payment Mode',
            'Payment Date',
            'Transaction Reference',
            'Notes',
            'Created By',
            'Created At'
        ];
    }

    public function map($expense): array
    {
        return [
            $expense->id,
            $expense->project->name,
            $expense->category->name,
            $expense->vendor->name,
            $expense->amount,
            $expense->tax,
            $expense->total_amount,
            ucwords(str_replace('_', ' ', $expense->payment_mode)),
            $expense->payment_date,
            $expense->transaction_reference,
            $expense->notes,
            $expense->creator->name,
            $expense->created_at->format('Y-m-d H:i:s')
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Make the first row bold
            1 => ['font' => ['bold' => true]],
        ];
    }
}
