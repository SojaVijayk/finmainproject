<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Collection;

class DeductionStatementExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $data;
    protected $columns;
    protected $columnLabels;

    public function __construct(array $data, array $columns, array $columnLabels)
    {
        $this->data = collect($data);
        $this->columns = $columns;
        $this->columnLabels = $columnLabels;
    }

    public function collection()
    {
        $totals = array_fill_keys($this->columns, 0);
        $exportData = [];

        foreach ($this->data as $index => $rowData) {
            $row = (array) $rowData;
            $exportRow = [];
            foreach ($this->columns as $col) {
                if ($col == 'slno') {
                    $exportRow[$col] = $index + 1;
                } elseif (in_array($col, ['name', 'designation', 'date_of_joining', 'bank_name', 'account_no', 'ifsc_code', 'branch', 'pan_number', 'address', 'email', 'mobile'])) {
                    $exportRow[$col] = $row[$col] ?? '-';
                } else {
                    $val = (float)($row[$col] ?? 0);
                    $exportRow[$col] = $val;
                    $totals[$col] += $val;
                }
            }
            $exportData[] = $exportRow;
        }

        if (count($exportData) > 0) {
            $footerRow = [];
            foreach ($this->columns as $col) {
                if ($col == 'slno') {
                    $footerRow[$col] = '';
                } elseif ($col == 'name') {
                    $footerRow[$col] = 'Total';
                } elseif (in_array($col, ['designation', 'date_of_joining', 'bank_name', 'account_no', 'ifsc_code', 'branch', 'pan_number', 'address', 'email', 'mobile'])) {
                    $footerRow[$col] = '';
                } else {
                    $footerRow[$col] = $totals[$col];
                }
            }
            $exportData[] = $footerRow;
        }

        return new Collection($exportData);
    }

    public function headings(): array
    {
        $headers = [];
        foreach ($this->columns as $col) {
            $headers[] = $this->columnLabels[$col] ?? ucwords(str_replace('_', ' ', $col));
        }
        return $headers;
    }
}
