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
            $table->decimal('tds_192_b', 10, 2)->default(0)->after('tds');
            $table->decimal('tds_194_j', 10, 2)->default(0)->after('tds_192_b');
            $table->decimal('esi_employer', 10, 2)->default(0)->after('esi');
            $table->decimal('lic_others', 10, 2)->default(0)->after('lic');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_payroll', function (Blueprint $table) {
            $table->dropColumn(['tds_192_b', 'tds_194_j', 'esi_employer', 'lic_others']);
        });
    }
};
