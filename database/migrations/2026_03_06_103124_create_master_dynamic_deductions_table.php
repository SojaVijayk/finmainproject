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
        Schema::create('master_dynamic_deductions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('default_calculation_type')->nullable(); // fixed, percent_gross, percent_custom
            $table->decimal('default_percentage', 8, 2)->nullable();
            $table->decimal('default_base_amount', 12, 2)->nullable();
            $table->decimal('default_amount', 12, 2)->nullable();
            $table->boolean('status')->default(1);
            $table->timestamps();
        });

        // Insert Default Master Deductions
        $defaultDeductions = [
            ['name' => 'PF', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'ESA', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'EDLI', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'LIC', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'TDS 194 J', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'TDS 192 B', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('master_dynamic_deductions')->insert($defaultDeductions);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_dynamic_deductions');
    }
};
