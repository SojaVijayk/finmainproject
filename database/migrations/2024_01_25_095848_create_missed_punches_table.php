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
        Schema::create('missed_punches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBiginteger('user_id')->unsigned();
            $table->foreign('user_id')->references('id')
                 ->on('users')->onDelete('cascade');
            $table->string('type');
            $table->date('date');
            $table->string('in_time');
            $table->string('out_time');
            $table->string('remark');
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
        Schema::dropIfExists('missed_punches');
    }
};
