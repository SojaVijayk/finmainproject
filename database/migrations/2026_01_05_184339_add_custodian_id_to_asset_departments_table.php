<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up()
  {
    Schema::table('asset_departments', function (Blueprint $table) {
      $table
        ->unsignedBigInteger('custodian_id')
        ->nullable()
        ->after('name');
      $table
        ->foreign('custodian_id')
        ->references('id')
        ->on('employees');
    });
  }

  public function down()
  {
    Schema::table('asset_departments', function (Blueprint $table) {
      $table->dropForeign(['custodian_id']);
      $table->dropColumn('custodian_id');
    });
  }
};
