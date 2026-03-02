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
        Schema::create('clients_projects', function (Blueprint $table) {
          $table->id();
          $table->unsignedBiginteger('client_id')->unsigned();
          $table->unsignedBiginteger('project_id')->unsigned();

          $table->foreign('client_id')->references('id')
               ->on('clients')->onDelete('cascade');
          $table->foreign('project_id')->references('id')
              ->on('projects')->onDelete('cascade');

          $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients_projects');
    }
};