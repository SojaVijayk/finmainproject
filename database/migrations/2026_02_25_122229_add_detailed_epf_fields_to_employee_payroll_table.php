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
            $table->decimal('epf_employers_share', 15, 2)->default(0)->after('employer_contribution');
            $table->decimal('edli_charges', 15, 2)->default(0)->after('epf_employers_share');
            $table->decimal('eligible_salary_revised', 15, 2)->default(0)->after('edli_charges');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_payroll', function (Blueprint $table) {
            $table->dropColumn(['epf_employers_share', 'edli_charges', 'eligible_salary_revised']);
        });
    }
};
