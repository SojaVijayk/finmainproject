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
            $table->decimal('admin_charge_percent', 5, 2)->default(7.5)->after('is_frozen');
            $table->decimal('gst_percent', 5, 2)->default(18.0)->after('admin_charge_percent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_payroll', function (Blueprint $table) {
            $table->dropColumn(['admin_charge_percent', 'gst_percent']);
        });
    }
};
