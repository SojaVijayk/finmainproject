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
        // Rename table if it exists as plural
        if (Schema::hasTable('project_employees') && !Schema::hasTable('project_employee')) {
            Schema::rename('project_employees', 'project_employee');
        }

        Schema::table('project_employee', function (Blueprint $table) {
            if (!Schema::hasColumn('project_employee', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->onDelete('cascade');
            }
            if (!Schema::hasColumn('project_employee', 'last_name')) {
                $table->string('last_name')->nullable()->after('name');
            }
            if (!Schema::hasColumn('project_employee', 'age')) {
                $table->integer('age')->nullable()->after('last_name');
            }
            if (!Schema::hasColumn('project_employee', 'dob')) {
                $table->date('dob')->nullable()->after('age');
            }
            if (!Schema::hasColumn('project_employee', 'address')) {
                $table->string('address')->nullable()->after('email');
            }
            if (!Schema::hasColumn('project_employee', 'p_id')) {
                $table->string('p_id')->nullable()->after('id');
            }
            if (!Schema::hasColumn('project_employee', 'service_id')) {
                $table->unsignedBigInteger('service_id')->nullable()->after('id');
            }
            if (!Schema::hasColumn('project_employee', 'salary_id')) {
                $table->unsignedBigInteger('salary_id')->nullable()->after('service_id');
            }
            if (!Schema::hasColumn('project_employee', 'deduction_id')) {
                $table->unsignedBigInteger('deduction_id')->nullable()->after('salary_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_employee', function (Blueprint $table) {
            $table->dropColumn(['age', 'dob', 'address', 'last_name', 'service_id', 'salary_id', 'deduction_id']);
        });
    }
};
