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
    Schema::table('movements', function (Blueprint $table) {
      $table
        ->string('reference_type')
        ->nullable()
        ->after('type'); // Requirement, Proposal, Project
      $table
        ->unsignedBigInteger('reference_id')
        ->nullable()
        ->after('reference_type');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('movements', function (Blueprint $table) {
      $table->dropColumn(['reference_type', 'reference_id']);
    });
  }
};
