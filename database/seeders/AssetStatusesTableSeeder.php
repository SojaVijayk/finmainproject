<?php

namespace Database\Seeders;

use App\Models\AssetStatus;
use Illuminate\Database\Seeder;

class AssetStatusesTableSeeder extends Seeder
{
    public function run()
    {
        $statuses = [
            ['name' => 'Available', 'color' => '#28a745'],
            ['name' => 'Assigned', 'color' => '#007bff'],
            ['name' => 'Maintenance', 'color' => '#ffc107'],
            ['name' => 'Non Functional', 'color' => '#6c757d'],
            ['name' => 'Lost', 'color' => '#dc3545']
        ];

        foreach ($statuses as $status) {
            AssetStatus::create($status);
        }
    }
}