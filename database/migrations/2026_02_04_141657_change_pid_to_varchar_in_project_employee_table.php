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
        Schema::table('project_employee', function (Blueprint $table) {
            // Using DB::statement to avoid doctrine/dbal dependency for simple column change
            DB::statement('ALTER TABLE project_employee MODIFY p_id VARCHAR(50)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_employee', function (Blueprint $table) {
             // Revert back to integer (Note: Only safe if data allows)
             DB::statement('ALTER TABLE project_employee MODIFY p_id INT');
        });
    }
};
