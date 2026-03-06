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
            $table->decimal('basic_pay', 10, 2)->nullable()->after('consolidated_pay');
            $table->decimal('da', 10, 2)->nullable()->after('basic_pay');
            $table->decimal('hra', 10, 2)->nullable()->after('da');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service', function (Blueprint $table) {
            $table->dropColumn(['basic_pay', 'da', 'hra']);
        });
    }
};
