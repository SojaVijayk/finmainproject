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
    Schema::table('asset_masters', function (Blueprint $table) {
      $table
        ->foreignId('asset_vendor_id')
        ->nullable()
        ->after('asset_category_id')
        ->constrained('asset_vendors')
        ->onDelete('set null');
      $table
        ->foreignId('asset_brand_id')
        ->nullable()
        ->after('asset_vendor_id')
        ->constrained('asset_brands')
        ->onDelete('set null');
      // We are keeping 'make' for now as nullable, might be useful for migration
      $table
        ->string('make')
        ->nullable()
        ->change();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('asset_masters', function (Blueprint $table) {
      $table->dropForeign(['asset_vendor_id']);
      $table->dropColumn('asset_vendor_id');
      $table->dropForeign(['asset_brand_id']);
      $table->dropColumn('asset_brand_id');
    });
  }
};
