<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained('expense_categories');
            $table->foreignId('vendor_id')->constrained();
            $table->decimal('amount', 15, 2);
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2);
            $table->enum('payment_mode', ['cash', 'bank_transfer', 'cheque', 'upi', 'other']);
            $table->date('payment_date');
            $table->string('transaction_reference')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('expenses');
    }
};
