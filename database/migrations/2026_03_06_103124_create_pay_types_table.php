<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pay_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('status')->default(1);
            $table->timestamps();
        });

        // Insert Default Pay Types
        $defaultPayTypes = [
            ['name' => 'Hourly pay', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Daily wage', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Weekly pay', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Bi-weekly', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Monthly', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Annual', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Per diem', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Shift based pay', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Consolidated pay', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
        ];
        
        DB::table('pay_types')->insert($defaultPayTypes);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pay_types');
    }
};
