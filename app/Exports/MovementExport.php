<?php

namespace App\Exports;

use App\Models\Movement;
use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MovementExport implements FromView, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public $data;
    public function __construct($data)
    {
        $this->data = $data;
    }

    public function view(): View
    {
        $data= $this->data;

            return view('exports.movement-export', [
                'data' => $data
            ]);

    }
    public function headings(): array
    {
        return [
            'Name',
            'Date From',
            'Time',
            'Date To',
            'Time',
            'Title',
            'Type',
            'Location',
            'Description',
            'Requested at',
            'status',
            'action by',
            'action at',
            'Remark',
        ];
    }
}