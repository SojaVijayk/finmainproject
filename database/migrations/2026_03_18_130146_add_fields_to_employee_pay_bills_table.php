<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_pay_bills', function (Blueprint $table) {
            $table->foreignId('pay_item_id')->constrained('pay_items')->onDelete('cascade');
            $table->foreignId('project_id')->nullable()->constrained('projects')->onDelete('set null');
            $table->string('salary_id')->index();
            $table->string('month');
            $table->integer('year');
            $table->string('to_month')->nullable();
            $table->integer('to_year')->nullable();
            $table->string('employment_type')->nullable();
            $table->string('status')->default('Draft');
        });
    }

    public function down(): void
    {
        Schema::table('employee_pay_bills', function (Blueprint $table) {
            $table->dropForeign(['pay_item_id']);
            $table->dropForeign(['project_id']);
            $table->dropColumn([
                'pay_item_id', 'project_id', 'salary_id', 'month', 'year', 'to_month', 'to_year', 'employment_type', 'status'
            ]);
        });
    }
};
