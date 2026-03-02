<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventsTable extends Migration
{
    public function up()
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->foreignId('event_type_id')->constrained();
            $table->foreignId('event_mode_id')->constrained();
            $table->foreignId('coordinator_id')->constrained('users');
            $table->foreignId('user_id')->constrained()->after('users');
            $table->foreignId('faculty_id')->nullable()->constrained('users');
            $table->integer('participants_count')->default(0);
            $table->enum('event_category', ['CMD', 'External'])->default('CMD');
            $table->string('external_entity')->nullable();
            $table->foreignId('venue_type_id')->constrained();
            $table->foreignId('venue_id')->nullable()->constrained();
            $table->string('external_venue')->nullable();
             $table->text('custom_amenities_request')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('events');
    }
}
