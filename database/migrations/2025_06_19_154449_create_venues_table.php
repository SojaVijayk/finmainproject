<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVenuesTable extends Migration
{
    public function up()
    {
        Schema::create('venues', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('venue_type_id')->constrained();
            $table->integer('seating_capacity');
            $table->text('amenities')->nullable();
            $table->enum('status', ['active', 'maintenance', 'blocked'])->default('active');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('venues');
    }
}
