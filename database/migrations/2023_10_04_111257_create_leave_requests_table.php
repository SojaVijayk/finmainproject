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
        Schema::create('leave_requests', function (Blueprint $table) {
          $table->id();
          $table->unsignedBiginteger('user_id')->unsigned();
          $table->foreign('user_id')->references('id')
               ->on('users')->onDelete('cascade');
               $table->unsignedBiginteger('leave_type_id')->unsigned();
               $table->foreign('leave_type_id')->references('id')
               ->on('leaves')->onDelete('cascade');
          $table->date('from');
          $table->date('to');
          $table->string('date_list');
          $table->string('duration');
          $table->string('description');
          $table->dateTime('requested_at');
          $table->integer('action_by');
          $table->dateTime('action_at');
          $table->smallInteger('status');
          $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
    }
};