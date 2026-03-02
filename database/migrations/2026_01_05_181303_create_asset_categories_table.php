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
    Schema::create('asset_categories', function (Blueprint $table) {
      $table->id();
      $table
        ->foreignId('asset_department_id')
        ->constrained('asset_departments')
        ->onDelete('cascade');
      $table->string('name');
      $table->string('prefix')->nullable();
      $table->boolean('is_depreciable')->default(0);
      $table->float('useful_life_years')->nullable();
      $table->decimal('salvage_value', 10, 2)->nullable();
      $table->json('specifications_schema')->nullable(); // For storing dynamic field definitions
      $table->timestamps();
      $table->softDeletes();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('asset_categories');
  }
};
