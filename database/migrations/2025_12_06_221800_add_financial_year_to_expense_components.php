<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('project_expense_components', function (Blueprint $table) {
            if (!Schema::hasColumn('project_expense_components', 'financial_year_id')) {
                $table->foreignId('financial_year_id')->nullable()->constrained('financial_years')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('project_expense_components', function (Blueprint $table) {
             if (Schema::hasColumn('project_expense_components', 'financial_year_id')) {
                $table->dropForeign(['financial_year_id']);
                $table->dropColumn('financial_year_id');
            }
        });
    }
};
