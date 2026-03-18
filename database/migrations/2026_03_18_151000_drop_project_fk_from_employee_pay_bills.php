<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the FK constraint that points to a non-existing/empty 'projects' table.
        // The actual project data lives in 'projectdemo', making this FK unresolvable.
        // We keep the column as a plain nullable integer to retain the data logically.
        try {
            DB::statement('ALTER TABLE employee_pay_bills DROP FOREIGN KEY employee_pay_bills_project_id_foreign');
        } catch (\Exception $e) {
            // Already dropped or doesn't exist - safe to continue
        }
    }

    public function down(): void
    {
        // Re-adding this constraint back is intentionally skipped since it was broken by design
    }
};
