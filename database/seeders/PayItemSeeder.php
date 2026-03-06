<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PayItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            ['name' => 'PF Tax',              'type' => 'Deduction',  'is_slab_based' => true,  'status' => true],
            ['name' => 'Festival Allowance',  'type' => 'Allowance',  'is_slab_based' => false, 'status' => true],
            ['name' => 'Bonus Allowance',     'type' => 'Allowance',  'is_slab_based' => false, 'status' => true],
        ];

        foreach ($items as $item) {
            \App\Models\PayItem::updateOrCreate(
                ['name' => $item['name']],
                $item
            );
        }
    }
}
