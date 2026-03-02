<?php

namespace App\Exports;

use App\Models\Asset\AssetMaster;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AssetRegisterExport implements FromCollection, WithHeadings, WithMapping
{
  protected $assets;

  public function __construct($assets)
  {
    $this->assets = $assets;
  }

  public function collection()
  {
    return $this->assets;
  }

  public function headings(): array
  {
    return [
      'Asset #',
      'Category',
      'Name',
      'Make',
      'Model',
      'Serial Number',
      'Purchase Date',
      'Cost',
      'Status',
      'Current Allocation',
      'Remarks',
    ];
  }

  public function map($asset): array
  {
    return [
      $asset->asset_number,
      $asset->category->name ?? '-',
      $asset->name,
      $asset->make,
      $asset->model,
      $asset->serial_number,
      $asset->purchase_date ? $asset->purchase_date->format('Y-m-d') : '-',
      $asset->purchase_cost,
      $asset->status,
      $asset->currentAllocation
        ? ($asset->currentAllocation->type == 'Employee'
          ? $asset->currentAllocation->employee->name
          : $asset->currentAllocation->location)
        : 'In Stock',
      '',
    ];
  }
}
