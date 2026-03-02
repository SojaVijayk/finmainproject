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
        Schema::create('employee_payroll', function (Blueprint $table) {
            $table->id();
            $table->string('p_id');
            $table->string('paymonth');
            $table->integer('year');
            $table->date('salary_start_date')->nullable();
            $table->date('salary_end_date')->nullable();
            
            // Earnings
            $table->decimal('basic_pay', 15, 2)->default(0);
            $table->decimal('da', 15, 2)->default(0);
            $table->decimal('hra', 15, 2)->default(0);
            $table->decimal('conveyance_allowance', 15, 2)->default(0);
            $table->decimal('medical_allowance', 15, 2)->default(0);
            $table->decimal('special_allowance', 15, 2)->default(0);
            $table->decimal('other_allowance', 15, 2)->default(0);
            $table->decimal('bonus', 15, 2)->default(0);
            $table->decimal('overtime_pay', 15, 2)->default(0);
            $table->decimal('attendance_bonus', 15, 2)->default(0);
            
            // Attendance
            $table->integer('total_working_days')->default(0);
            $table->integer('days_worked')->default(0);
            $table->integer('lop_days')->default(0);
            
            // Deductions
            $table->decimal('pf', 15, 2)->default(0);
            $table->decimal('epf', 15, 2)->default(0);
            $table->decimal('esi', 15, 2)->default(0);
            $table->decimal('lic', 15, 2)->default(0);
            $table->decimal('professional_tax', 15, 2)->default(0);
            $table->decimal('tds', 15, 2)->default(0);
            $table->decimal('loan_deduction', 15, 2)->default(0);
            $table->decimal('gdf', 15, 2)->default(0);
            $table->decimal('gpf', 15, 2)->default(0);
            $table->decimal('others', 15, 2)->default(0);
            
            // Totals
            $table->decimal('gross_salary', 15, 2)->default(0);
            $table->decimal('total_deductions', 15, 2)->default(0);
            $table->decimal('net_salary', 15, 2)->default(0);
            
            $table->timestamps();
            
            $table->unique(['p_id', 'paymonth', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_payroll');
    }
};
