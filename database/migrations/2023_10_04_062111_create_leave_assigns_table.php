<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('leave_assigns', function (Blueprint $table) {
          $table->id();
          $table->unsignedBiginteger('leave_type')->unsigned();
          $table->foreign('leave_type')->references('id')
          ->on('leaves')->onDelete('cascade');
          $table->double('total_credit');
          $table->smallInteger('employment_type');
          $table->smallInteger('status');
          $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_assigns');
    }
};