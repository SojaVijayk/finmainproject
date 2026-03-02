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
        Schema::create('proposals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requirement_id')->constrained()->onDelete('cascade');
            $table->decimal('budget', 15, 2);
            $table->integer('tenure_days')->nullable();
            $table->integer('tenure_months')->nullable();
            $table->integer('tenure_years')->nullable();
            $table->date('expected_start_date');
            $table->date('expected_end_date');
            $table->decimal('estimated_expense', 15, 2);
            $table->decimal('revenue', 15, 2);
            $table->text('technical_details')->nullable();
            $table->text('methodology')->nullable();
            $table->unsignedTinyInteger('status')->default(0)->comment('0:created,1:send to director for approval,2:approved by director,3:rejected,4:return for clarification');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proposals');
    }
};