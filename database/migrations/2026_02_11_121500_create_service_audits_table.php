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
        if (!Schema::hasTable('service_audits')) {
            Schema::create('service_audits', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('service_id');
                $table->string('field_name');
                $table->text('old_value')->nullable();
                $table->text('new_value')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable(); // User ID who made the change
                $table->timestamps();
    
                // Foreign key to 'service' table (singular)
                $table->foreign('service_id')->references('id')->on('service')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_audits');
    }
};
