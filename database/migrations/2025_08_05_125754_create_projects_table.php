<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('project_code')->unique();
            $table->foreignId('requirement_id')->constrained();
            $table->foreignId('proposal_id')->constrained();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('budget', 15, 2);
            $table->decimal('estimated_expense', 15, 2);
            $table->decimal('revenue', 15, 2);
            $table->unsignedTinyInteger('status')->default(0)->comment('0:initiated,1:ongoing,2:completed');
            $table->foreignId('project_investigator_id')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('projects');
    }
};
