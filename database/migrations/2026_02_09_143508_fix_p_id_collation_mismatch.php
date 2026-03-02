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
        // Standardize p_id collation to utf8mb4_unicode_ci across all related tables
        DB::statement('ALTER TABLE project_employee MODIFY p_id VARCHAR(50) COLLATE utf8mb4_unicode_ci');
        DB::statement('ALTER TABLE service MODIFY p_id VARCHAR(255) COLLATE utf8mb4_unicode_ci');
        DB::statement('ALTER TABLE salary MODIFY p_id VARCHAR(255) COLLATE utf8mb4_unicode_ci');
        DB::statement('ALTER TABLE deduction MODIFY p_id VARCHAR(255) COLLATE utf8mb4_unicode_ci');
        DB::statement('ALTER TABLE employee_payroll MODIFY p_id VARCHAR(255) COLLATE utf8mb4_unicode_ci');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to general collation if needed, though unicode is preferred
        DB::statement('ALTER TABLE project_employee MODIFY p_id VARCHAR(50) COLLATE utf8mb4_general_ci');
    }
};
