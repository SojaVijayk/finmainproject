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
        try { \Illuminate\Support\Facades\DB::statement('ALTER TABLE employee_pay_bills DROP INDEX emp_period_unique'); } catch (\Exception $e) {}
        try { \Illuminate\Support\Facades\DB::statement('ALTER TABLE employee_pay_bills DROP COLUMN p_id'); } catch (\Exception $e) {}
        try { \Illuminate\Support\Facades\DB::statement('ALTER TABLE employee_pay_bills DROP COLUMN period_from'); } catch (\Exception $e) {}
        try { \Illuminate\Support\Facades\DB::statement('ALTER TABLE employee_pay_bills DROP COLUMN period_to'); } catch (\Exception $e) {}
        try { \Illuminate\Support\Facades\DB::statement('ALTER TABLE employee_pay_bills DROP COLUMN total_gross_salary'); } catch (\Exception $e) {}
        try { \Illuminate\Support\Facades\DB::statement('ALTER TABLE employee_pay_bills DROP COLUMN pf_tax'); } catch (\Exception $e) {}
        try { \Illuminate\Support\Facades\DB::statement('ALTER TABLE employee_pay_bills DROP COLUMN festival_allowance'); } catch (\Exception $e) {}
        try { \Illuminate\Support\Facades\DB::statement('ALTER TABLE employee_pay_bills DROP COLUMN bonus_allowance'); } catch (\Exception $e) {}
        try { \Illuminate\Support\Facades\DB::statement('ALTER TABLE employee_pay_bills DROP COLUMN final_adjustment'); } catch (\Exception $e) {}
        try { \Illuminate\Support\Facades\DB::statement('ALTER TABLE employee_pay_bills DROP COLUMN net_amount'); } catch (\Exception $e) {}
        try { \Illuminate\Support\Facades\DB::statement('ALTER TABLE employee_pay_bills DROP COLUMN amount'); } catch (\Exception $e) {}
        try { \Illuminate\Support\Facades\DB::statement('ALTER TABLE employee_pay_bills DROP COLUMN pay_period'); } catch (\Exception $e) {}
        try { \Illuminate\Support\Facades\DB::statement('ALTER TABLE employee_pay_bills DROP COLUMN payment_status'); } catch (\Exception $e) {}
        try { \Illuminate\Support\Facades\DB::statement('ALTER TABLE employee_pay_bills DROP COLUMN pay_period'); } catch (\Exception $e) {}
        try { \Illuminate\Support\Facades\DB::statement('ALTER TABLE employee_pay_bills DROP COLUMN payment_status'); } catch (\Exception $e) {}
    }

    public function down(): void
    {
        Schema::table('employee_pay_bills', function (Blueprint $table) {
            // Reversing is destructive in this case, leave blank
        });
    }
};
