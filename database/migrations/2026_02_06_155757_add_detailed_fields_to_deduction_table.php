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
        Schema::table('deduction', function (Blueprint $table) {
            if (!Schema::hasColumn('deduction', 'epf')) {
                $table->decimal('epf', 15, 2)->default(0)->after('pf');
            }
            if (!Schema::hasColumn('deduction', 'lic')) {
                $table->decimal('lic', 15, 2)->default(0)->after('esi');
            }
            if (!Schema::hasColumn('deduction', 'tds')) {
                $table->decimal('tds', 15, 2)->default(0)->after('professional_tax');
            }
            if (!Schema::hasColumn('deduction', 'loan_deduction')) {
                $table->decimal('loan_deduction', 15, 2)->default(0)->after('tds');
            }
            if (!Schema::hasColumn('deduction', 'gdf')) {
                $table->decimal('gdf', 15, 2)->default(0)->after('loan_deduction');
            }
            if (!Schema::hasColumn('deduction', 'gpf')) {
                $table->decimal('gpf', 15, 2)->default(0)->after('gdf');
            }
            if (!Schema::hasColumn('deduction', 'others')) {
                $table->decimal('others', 15, 2)->default(0)->after('gpf');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deduction', function (Blueprint $table) {
            $table->dropColumn(['epf', 'lic', 'tds', 'loan_deduction', 'gdf', 'gpf', 'others']);
        });
    }
};
