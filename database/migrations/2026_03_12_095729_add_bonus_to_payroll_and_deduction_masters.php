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
        Schema::table('deduction_masters', function (Blueprint $table) {
            $table->boolean('bonus')->default(0)->after('festival_allowance');
            $table->decimal('bonus_value', 15, 2)->default(0)->after('bonus');
            $table->string('bonus_type')->nullable()->after('bonus_value');
            $table->decimal('bonus_amount', 15, 2)->default(0)->after('bonus_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deduction_masters', function (Blueprint $table) {
            $table->dropColumn(['bonus', 'bonus_value', 'bonus_type', 'bonus_amount']);
        });
    }
};
