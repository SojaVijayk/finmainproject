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
    Schema::create('bank_transactions', function (Blueprint $table) {
      $table->id();
      $table
        ->foreignId('finance_bank_account_id')
        ->constrained('finance_bank_accounts')
        ->onDelete('cascade');
      $table->enum('type', ['credit', 'debit']);
      $table->decimal('amount', 15, 2);
      $table->decimal('balance_after', 15, 2);
      $table->date('transaction_date');
      $table->string('category')->nullable(); // Invoice, Expense, Manual, Bulk
      $table->text('description')->nullable();
      $table->nullableMorphs('reference'); // polymorphic for InvoicePayment, Expense, etc.
      $table->timestamps();
      $table->softDeletes();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('bank_transactions');
  }
};
