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
    Schema::create('daily_bank_balances', function (Blueprint $table) {
      $table->id();
      $table
        ->foreignId('finance_bank_account_id')
        ->constrained()
        ->onDelete('cascade');
      $table->date('date');
      $table->decimal('opening_balance', 15, 2)->default(0);
      $table->decimal('receipts', 15, 2)->default(0);
      $table->decimal('payments', 15, 2)->default(0);
      $table->decimal('closing_balance', 15, 2)->default(0);
      $table->timestamps();

      $table->unique(['finance_bank_account_id', 'date']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('daily_bank_balances');
  }
};
