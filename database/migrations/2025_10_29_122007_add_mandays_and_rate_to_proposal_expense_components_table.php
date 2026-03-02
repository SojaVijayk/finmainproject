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
        Schema::table('proposal_expense_components', function (Blueprint $table) {
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         Schema::table('proposal_expense_components', function (Blueprint $table) {
        $table->string('group_name')->nullable()->after('expense_category_id');
        $table->integer('mandays')->nullable()->after('group');
        $table->decimal('rate', 10, 2)->nullable()->after('mandays');

    });
    }
};