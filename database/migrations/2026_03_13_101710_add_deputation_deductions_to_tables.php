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
            // Adding missing columns to employee_payroll
            if (!Schema::hasColumn('employee_payroll', 'medisep')) {
                $table->decimal('medisep', 10, 2)->default(0)->after('lic_others');
            }
            if (!Schema::hasColumn('employee_payroll', 'sli1')) {
                $table->decimal('sli1', 10, 2)->default(0)->after('medisep');
            }
            if (!Schema::hasColumn('employee_payroll', 'sli2')) {
                $table->decimal('sli2', 10, 2)->default(0)->after('sli1');
            }
            if (!Schema::hasColumn('employee_payroll', 'sli3')) {
                $table->decimal('sli3', 10, 2)->default(0)->after('sli2');
            }
            if (!Schema::hasColumn('employee_payroll', 'gis')) {
                $table->decimal('gis', 10, 2)->default(0)->after('sli3');
            }
            if (!Schema::hasColumn('employee_payroll', 'gpais')) {
                $table->decimal('gpais', 10, 2)->default(0)->after('gis');
            }
        });

        Schema::table('deduction_masters', function (Blueprint $table) {
            // Adding config columns to deduction_masters for new fields
            $fields = ['medisep', 'gpf', 'sli1', 'sli2', 'sli3', 'gis', 'gpais'];
            
            foreach ($fields as $field) {
                if (!Schema::hasColumn('deduction_masters', $field)) {
                    $table->boolean($field)->default(false);
                }
                if (!Schema::hasColumn('deduction_masters', $field . '_value')) {
                    $table->decimal($field . '_value', 15, 2)->nullable();
                }
                if (!Schema::hasColumn('deduction_masters', $field . '_type')) {
                    $table->string($field . '_type')->default('amount');
                }
                if (!Schema::hasColumn('deduction_masters', $field . '_amount')) {
                    $table->decimal($field . '_amount', 15, 2)->nullable();
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_payroll', function (Blueprint $table) {
            $table->dropColumn(['medisep', 'sli1', 'sli2', 'sli3', 'gis', 'gpais']);
        });

        Schema::table('deduction_masters', function (Blueprint $table) {
            $fields = ['medisep', 'gpf', 'sli1', 'sli2', 'sli3', 'gis', 'gpais'];
            $cols = [];
            foreach ($fields as $field) {
                $cols[] = $field;
                $cols[] = $field . '_value';
                $cols[] = $field . '_type';
                $cols[] = $field . '_amount';
            }
            $table->dropColumn($cols);
        });
    }
};
