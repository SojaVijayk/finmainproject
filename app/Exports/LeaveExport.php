<?php

namespace App\Exports;

use App\Models\LeaveRequestDetails;
use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithHeadings;

class LeaveExport implements FromView, WithHeadings
{
  public $data;
  public function __construct($data)
  {
      $this->data = $data;
  }

  public function view(): View
  {
      $data= $this->data;

          return view('exports.leave-export', [
              'data' => $data
          ]);

  }
  public function headings(): array
  {
      return [
          'Name',
          'Leave Type',
          'Date',
          'Leave Day Type',
          'Requested at',
          'status',
          'action by',
          'action at',
          'Remark',
      ];
  }
}