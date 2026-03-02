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
        Schema::table('employee_payroll', function (Blueprint $table) {
            $table->decimal('cl_days', 4, 2)->default(0)->after('salary_id');
            $table->decimal('sl_days', 4, 2)->default(0)->after('cl_days');
            $table->decimal('pl_days', 4, 2)->default(0)->after('sl_days');
            $table->decimal('other_leave_days', 4, 2)->default(0)->after('pl_days');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_payroll', function (Blueprint $table) {
            $table->dropColumn(['cl_days', 'sl_days', 'pl_days', 'other_leave_days']);
        });
    }
};
