<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('employee_dynamic_deductions', function (Blueprint $table) {
            $table->id();
            $table->string('p_id');
            $table->string('deduction_name');
            $table->string('calculation_type'); // 'fixed', 'percent_gross', 'percent_custom'
            $table->decimal('percentage', 5, 2)->nullable();
            $table->decimal('base_amount', 15, 2)->nullable();
            $table->decimal('amount', 15, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_dynamic_deductions');
    }
};
