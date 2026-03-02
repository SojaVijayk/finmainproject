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
        // Create financial_years table
Schema::create('financial_years', function (Blueprint $table) {
    $table->id();
    $table->string('name'); // e.g., "2024-2025"
    $table->string('short_name')->nullable(); // e.g., "FY24-25"
    $table->date('start_date');
    $table->date('end_date');
    $table->boolean('is_active')->default(true);
    $table->timestamps();

    $table->index(['start_date', 'end_date']);
    $table->index('is_active');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_years');
    }
};