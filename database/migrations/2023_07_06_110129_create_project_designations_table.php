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
        Schema::create('project_designations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBiginteger('user_type_id')->unsigned();
            $table->foreign('user_type_id')->references('id')
                 ->on('project_user_types')->onDelete('cascade');
            $table->string('designation');
            $table->smallInteger('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_designations');
    }
};