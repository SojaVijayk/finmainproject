<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tapal_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tapal_id')->constrained()->onDelete('cascade');
            $table->foreignId('from_user_id')->constrained('users');
            $table->foreignId('to_user_id')->constrained('users');
            $table->text('remarks')->nullable();
            $table->enum('status', ['Pending', 'Accepted', 'Completed', 'Notified'])->default('Pending');
            $table->boolean('is_assignment')->default(false);
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tapal_movements');
    }
};
