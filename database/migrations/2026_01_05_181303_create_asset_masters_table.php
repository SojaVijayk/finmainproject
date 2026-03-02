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
    Schema::create('asset_masters', function (Blueprint $table) {
      $table->id();
      $table
        ->foreignId('asset_category_id')
        ->constrained('asset_categories')
        ->onDelete('restrict');
      $table->string('asset_number')->unique();
      $table->string('name');
      $table->string('make')->nullable();
      $table->string('model')->nullable();
      $table->string('serial_number')->nullable();
      $table->date('purchase_date')->nullable();
      $table->decimal('purchase_cost', 15, 2)->nullable();
      $table->date('warranty_expiry_date')->nullable();
      $table->string('condition')->default('New'); // New, Good, Fair, Poor
      $table->string('status')->default('Available'); // Available, Issued, Scrapped, Under Repair
      $table->json('specifications')->nullable();
      $table->string('qr_code_path')->nullable();
      $table->timestamps();
      $table->softDeletes();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('asset_masters');
  }
};
