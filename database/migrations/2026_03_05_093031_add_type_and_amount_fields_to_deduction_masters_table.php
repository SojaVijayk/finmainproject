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
            $table->string('tds_type')->default('amount');
            $table->decimal('tds_amount', 15, 2)->nullable();
            
            $table->string('epf_type')->default('amount');
            $table->decimal('epf_amount', 15, 2)->nullable();
            
            $table->string('pf_type')->default('amount');
            $table->decimal('pf_amount', 15, 2)->nullable();
            
            $table->string('lic_type')->default('amount');
            $table->decimal('lic_amount', 15, 2)->nullable();
            
            $table->string('edli_type')->default('amount');
            $table->decimal('edli_amount', 15, 2)->nullable();
            
            $table->string('other_type')->default('amount');
            $table->decimal('other_amount', 15, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deduction_masters', function (Blueprint $table) {
            $table->dropColumn([
                'tds_type', 'tds_amount',
                'epf_type', 'epf_amount',
                'pf_type', 'pf_amount',
                'lic_type', 'lic_amount',
                'edli_type', 'edli_amount',
                'other_type', 'other_amount'
            ]);
        });
    }
};
