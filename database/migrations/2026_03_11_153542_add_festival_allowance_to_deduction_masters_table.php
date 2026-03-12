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
            $table->boolean('festival_allowance')->default(false);
            $table->decimal('festival_allowance_value', 15, 2)->nullable();
            $table->string('festival_allowance_type')->default('amount');
            $table->decimal('festival_allowance_amount', 15, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deduction_masters', function (Blueprint $table) {
            $table->dropColumn(['festival_allowance', 'festival_allowance_value', 'festival_allowance_type', 'festival_allowance_amount']);
        });
    }
};
