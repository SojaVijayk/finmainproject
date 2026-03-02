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
        Schema::table('project_employee', function (Blueprint $table) {
            $table->unsignedBigInteger('designation_id')->nullable()->change();
            $table->string('documents')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_employee', function (Blueprint $table) {
             // Reverting nullable is risky if data exists with nulls, but for strict down:
            //$table->unsignedBigInteger('designation_id')->nullable(false)->change();
            $table->dropColumn('documents');
        });
    }
};
