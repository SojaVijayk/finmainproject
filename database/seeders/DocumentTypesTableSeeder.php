<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DocumentType;

class DocumentTypesTableSeeder extends Seeder
{
    public function run()
    {
        $types = [
            ['name' => 'Work Order', 'prefix' => 'WO'],
            ['name' => 'Purchase Order', 'prefix' => 'PO'],
            ['name' => 'Payment Order', 'prefix' => 'PAY'],
            ['name' => 'Appointment Letter', 'prefix' => 'AL'],
            ['name' => 'General Letter', 'prefix' => 'GL'],
            ['name' => 'RTI Reply', 'prefix' => 'RTI'],
        ];

        foreach ($types as $type) {
            DocumentType::create($type);
        }
    }
}
