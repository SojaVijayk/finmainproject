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
    Schema::create('asset_allocations', function (Blueprint $table) {
      $table->id();
      $table
        ->foreignId('asset_id')
        ->constrained('asset_masters')
        ->onDelete('cascade');
      $table->enum('type', ['Employee', 'Location']);
      $table
        ->foreignId('employee_id')
        ->nullable()
        ->constrained('employees')
        ->onDelete('set null');
      $table->string('location')->nullable();
      $table->foreignId('issued_by')->constrained('users'); // User who issued the asset
      $table->timestamp('issued_at')->useCurrent();
      $table->date('expected_return_at')->nullable();
      $table->timestamp('returned_at')->nullable();
      $table->text('return_remarks')->nullable();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('asset_allocations');
  }
};
