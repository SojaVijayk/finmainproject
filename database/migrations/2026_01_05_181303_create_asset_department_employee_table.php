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
    Schema::create('asset_department_employee', function (Blueprint $table) {
      $table->id();
      $table
        ->foreignId('asset_department_id')
        ->constrained('asset_departments')
        ->onDelete('cascade');
      $table
        ->foreignId('employee_id')
        ->constrained('employees')
        ->onDelete('cascade');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('asset_department_employee');
  }
};
