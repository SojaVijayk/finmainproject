<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tapals', function (Blueprint $table) {
            $table->id();
            $table->string('tapal_number')->unique();
            $table->enum('type', ['inward', 'outward']);
            $table->date('inward_date');
            $table->date('received_date');
            $table->enum('inward_mode', ['Speed Post', 'Post', 'Courier', 'By Mail', 'By Hand']);
            $table->string('mail_id')->nullable();
            $table->string('from_name')->nullable();
            $table->string('from_address')->nullable();
            $table->string('from_department')->nullable();
            $table->string('from_mobile')->nullable();
            $table->text('from_person_details')->nullable();
            $table->string('ref_number')->nullable();
            $table->date('letter_date')->nullable();
            $table->string('subject');
            $table->text('description')->nullable();
            $table->foreignId('current_holder_id')->constrained('users');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tapals');
    }
};
