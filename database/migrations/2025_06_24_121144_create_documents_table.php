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
        Schema::create('documents', function (Blueprint $table) {
    $table->id();
    $table->string('document_number')->unique();
    $table->enum('number_type', ['DS', 'General']);
    $table->foreignId('document_type_id')->constrained();
    $table->foreignId('user_id')->constrained(); // creator
    $table->foreignId('authorized_person_id')->constrained('users');
    $table->foreignId('code_id')->constrained('document_codes');
    $table->string('to_address_details');
    $table->string('subject');
    $table->string('project_details')->nullable();
    $table->integer('sequence_number');
    $table->year('year');
    $table->enum('status', ['created', 'active', 'cancelled'])->default('created');
    $table->text('cancellation_reason')->nullable();
    $table->foreignId('cancelled_by')->nullable()->constrained('users');
    $table->timestamp('cancelled_at')->nullable();
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};