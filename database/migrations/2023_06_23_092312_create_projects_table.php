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
        Schema::create('projects', function (Blueprint $table) {
          $table->bigIncrements('id'); // permission id
          $table->string('project_name');
          $table->string('description');
          $table->string('type');
          $table->string('leads');
          $table->string('members');
          $table->string('total_cost');
          $table->string('members');
          $table->integer('client_contact_person');
          $table->integer('initiated_by');
          $table->string('contract_staff_strength');
          $table->string('field_staff_strength');
          $table->string('project_staff_strength');
          $table->string('tenure_year');
          $table->string('tenure_month');
          $table->string('tenure_days');
          $table->date('expected_start_date');
          $table->date('expected_end_date');
          $table->text('additional_support');
          $table->text('remarks');


            $table->timestamps();
            $table->unique(['project_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};