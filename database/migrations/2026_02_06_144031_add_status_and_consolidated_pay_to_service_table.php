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
        Schema::table('service', function (Blueprint $table) {
            $table->tinyInteger('status')->default(1)->comment('1: Active, 0: Deactive')->after('end_date');
            $table->decimal('consolidated_pay', 15, 2)->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service', function (Blueprint $table) {
            $table->dropColumn(['status', 'consolidated_pay']);
        });
    }
};
