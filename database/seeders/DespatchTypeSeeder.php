<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DespatchType;

class DespatchTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'By Hand', 'requires_tracking' => false, 'requires_ack' => false],
            ['name' => 'Mail', 'requires_mail_id' => true],
            ['name' => 'Ordinary Post'],
            ['name' => 'Speed Post', 'requires_tracking' => true],
            ['name' => 'Speed Post with Ack', 'requires_tracking' => true, 'requires_ack' => true],
            ['name' => 'Courier'],
            ['name' => 'Registered Post', 'requires_tracking' => true],
            ['name' => 'Registered Post with Ack', 'requires_tracking' => true, 'requires_ack' => true],
        ];

        foreach ($types as $type) {
            DespatchType::create($type);
        }
    }
}