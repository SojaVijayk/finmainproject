<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('fund_requirements', function (Blueprint $table) {
      $table->id();
      $table->string('title');
      $table->decimal('amount', 15, 2);
      $table->date('due_date');
      $table->boolean('is_recurring')->default(false);
      $table->string('frequency')->nullable(); // monthly, weekly, etc.
      $table->string('status')->default('pending'); // pending, approved, paid
      $table->text('description')->nullable();
      $table->timestamps();
      $table->softDeletes();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('fund_requirements');
  }
};
