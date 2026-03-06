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
            // TDS 192 B
            $table->boolean('tds_192_b')->default(false);
            $table->decimal('tds_192_b_value', 15, 2)->nullable();
            $table->string('tds_192_b_type')->default('amount');
            $table->decimal('tds_192_b_amount', 15, 2)->nullable();

            // TDS 194 J
            $table->boolean('tds_194_j')->default(false);
            $table->decimal('tds_194_j_value', 15, 2)->nullable();
            $table->string('tds_194_j_type')->default('amount');
            $table->decimal('tds_194_j_amount', 15, 2)->nullable();

            // Professional Tax
            $table->boolean('professional_tax')->default(false);
            $table->decimal('professional_tax_value', 15, 2)->nullable();
            $table->string('professional_tax_type')->default('amount');
            $table->decimal('professional_tax_amount', 15, 2)->nullable();

            // ESI Employer
            $table->boolean('esi_employer')->default(false);
            $table->decimal('esi_employer_value', 15, 2)->nullable();
            $table->string('esi_employer_type')->default('amount');
            $table->decimal('esi_employer_amount', 15, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deduction_masters', function (Blueprint $table) {
            $table->dropColumn([
                'tds_192_b', 'tds_192_b_value', 'tds_192_b_type', 'tds_192_b_amount',
                'tds_194_j', 'tds_194_j_value', 'tds_194_j_type', 'tds_194_j_amount',
                'professional_tax', 'professional_tax_value', 'professional_tax_type', 'professional_tax_amount',
                'esi_employer', 'esi_employer_value', 'esi_employer_type', 'esi_employer_amount'
            ]);
        });
    }
};
