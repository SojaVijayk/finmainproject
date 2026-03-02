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
        Schema::table('salary', function (Blueprint $table) {
            if (!Schema::hasColumn('salary', 'paymonth')) {
                $table->string('paymonth')->nullable()->after('p_id');
            }
            if (!Schema::hasColumn('salary', 'year')) {
                $table->integer('year')->nullable()->after('paymonth');
            }
            if (!Schema::hasColumn('salary', 'salary_start_date')) {
                $table->date('salary_start_date')->nullable()->after('year');
            }
            if (!Schema::hasColumn('salary', 'salary_end_date')) {
                $table->date('salary_end_date')->nullable()->after('salary_start_date');
            }
            if (!Schema::hasColumn('salary', 'da')) {
                $table->decimal('da', 15, 2)->default(0)->after('hra');
            }
            if (!Schema::hasColumn('salary', 'conveyance_allowance')) {
                $table->decimal('conveyance_allowance', 15, 2)->default(0)->after('da');
            }
            if (!Schema::hasColumn('salary', 'medical_allowance')) {
                $table->decimal('medical_allowance', 15, 2)->default(0)->after('conveyance_allowance');
            }
            if (!Schema::hasColumn('salary', 'special_allowance')) {
                $table->decimal('special_allowance', 15, 2)->default(0)->after('medical_allowance');
            }
            if (!Schema::hasColumn('salary', 'bonus')) {
                $table->decimal('bonus', 15, 2)->default(0)->after('other_allowance');
            }
            if (!Schema::hasColumn('salary', 'overtime_pay')) {
                $table->decimal('overtime_pay', 15, 2)->default(0)->after('bonus');
            }
            if (!Schema::hasColumn('salary', 'attendance_bonus')) {
                $table->decimal('attendance_bonus', 15, 2)->default(0)->after('overtime_pay');
            }
            if (!Schema::hasColumn('salary', 'total_working_days')) {
                $table->integer('total_working_days')->default(0)->after('attendance_bonus');
            }
            if (!Schema::hasColumn('salary', 'days_worked')) {
                $table->integer('days_worked')->default(0)->after('total_working_days');
            }
            if (!Schema::hasColumn('salary', 'lop_days')) {
                $table->integer('lop_days')->default(0)->after('days_worked');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salary', function (Blueprint $table) {
            $table->dropColumn([
                'paymonth', 'year', 'salary_start_date', 'salary_end_date',
                'da', 'conveyance_allowance', 'medical_allowance', 'special_allowance',
                'bonus', 'overtime_pay', 'attendance_bonus',
                'total_working_days', 'days_worked', 'lop_days'
            ]);
        });
    }
};
