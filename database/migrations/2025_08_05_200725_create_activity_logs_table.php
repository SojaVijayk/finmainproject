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
       Schema::create('activity_logs', function (Blueprint $table) {
    $table->id();
    $table->string('log_name')->nullable();
    $table->text('description');
    $table->string('event')->nullable();
    $table->unsignedBigInteger('subject_id')->nullable();
    $table->string('subject_type')->nullable();
    $table->unsignedBigInteger('user_id')->nullable();
    $table->json('properties')->nullable();
    $table->timestamps();

    $table->index('subject_id');
    $table->index('subject_type');
    $table->index('user_id');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};