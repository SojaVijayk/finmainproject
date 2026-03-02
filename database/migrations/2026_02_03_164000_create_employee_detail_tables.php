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
        Schema::create('service', function (Blueprint $table) {
            $table->id();
            $table->string('p_id')->index(); // Reference to project_employee p_id
            $table->string('department')->nullable();
            $table->string('employment_type')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();
        });

        Schema::create('salary', function (Blueprint $table) {
            $table->id();
            $table->string('p_id')->index(); // Reference to project_employee p_id
            $table->decimal('basic_pay', 15, 2)->default(0);
            $table->decimal('hra', 15, 2)->default(0);
            $table->decimal('other_allowance', 15, 2)->default(0);
            $table->decimal('gross_salary', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('deduction', function (Blueprint $table) {
            $table->id();
            $table->string('p_id')->index(); // Reference to project_employee p_id
            $table->decimal('pf', 15, 2)->default(0);
            $table->decimal('esi', 15, 2)->default(0);
            $table->decimal('professional_tax', 15, 2)->default(0);
            $table->decimal('total_deductions', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service');
        Schema::dropIfExists('salary');
        Schema::dropIfExists('deduction');
    }
};
