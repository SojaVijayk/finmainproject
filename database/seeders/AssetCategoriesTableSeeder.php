<?php

namespace Database\Seeders;

use App\Models\AssetCategory;
use Illuminate\Database\Seeder;

class AssetCategoriesTableSeeder extends Seeder
{
    public function run()
    {
        $categories = [
            ['name' => 'Laptop', 'description' => 'Portable computers'],
            ['name' => 'Desktop', 'description' => 'Desktop computers'],
            ['name' => 'Printer', 'description' => 'Printing devices'],
            ['name' => 'Monitor', 'description' => 'Computer displays'],
            ['name' => 'Network Device', 'description' => 'Routers, switches, etc.'],
            ['name' => 'Accessory', 'description' => 'Keyboards, mice, etc.'],
            ['name' => 'Consumable', 'description' => 'Printer toner, paper, etc.']
        ];

        foreach ($categories as $category) {
            AssetCategory::create($category);
        }
    }
}
