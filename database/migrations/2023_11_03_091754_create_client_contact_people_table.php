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
        Schema::create('client_contact_people', function (Blueprint $table) {
            $table->id();
            $table->unsignedBiginteger('client_id')->unsigned();
            $table->foreign('client_id')->references('id')
                 ->on('clients')->onDelete('cascade');
                 $table->string('name');
                 $table->string('designation');
                 $table->string('email');
                 $table->string('address');
                 $table->string('mobile');
            $table->smallInteger('status');
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_contact_people');
    }
};