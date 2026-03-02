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
         Schema::create('project_yearly_budgets', function (Blueprint $table) {
            $table->id();

            // Foreign key to projects table
            $table->foreignId('project_id')
                  ->constrained('projects')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            // Financial year (e.g., 2024-25, 2025-26)
            $table->foreignId('financial_year_id')->constrained('financial_years'); // Add this line

            // Budget amount for this financial year
            $table->decimal('amount', 15, 2)
                  ->default(0.00)
                  ->comment('Budget amount for this financial year');

            // Optional notes
            $table->text('notes')
                  ->nullable()
                  ->comment('Additional notes about this yearly budget');

            // Timestamps
            $table->timestamps();

            // Indexes for better performance
            $table->index(['project_id', 'financial_year_id']);
            $table->index('financial_year_id');

            // Ensure unique combination of project and financial year
            $table->unique(['project_id', 'financial_year_id'], 'project_year_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_yearly_budgets');
    }
};
