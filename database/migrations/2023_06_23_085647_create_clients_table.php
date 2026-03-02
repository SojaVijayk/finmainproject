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
        Schema::create('clients', function (Blueprint $table) {
          $table->bigIncrements('id'); // permission id
          $table->string('client_name');
          $table->string('email');
          $table->string('address');
          $table->string('phone');      // For MySQL 8.0 use string('name', 125);
            $table->timestamps();
            $table->unique(['client_name','email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};