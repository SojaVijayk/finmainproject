<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_pay_bill_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_pay_bill_id')->constrained('employee_pay_bills')->onDelete('cascade');
            $table->string('p_id');
            $table->decimal('base_salary', 10, 2)->default(0);
            $table->decimal('actual_salary', 10, 2)->default(0);
            $table->decimal('total_period_salary', 10, 2)->default(0);
            $table->decimal('adjusted_amount', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_pay_bill_details');
    }
};
