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
        Schema::create('project_milestones', function (Blueprint $table) {
            $table->id();
            $table->string('milestone');
            $table->datetime('due_date');
            $table->datetime('achived_at');
            $table->unsignedBiginteger('project_id')->unsigned();
            $table->foreign('project_id')->references('id')
                 ->on('projects')->onDelete('cascade');
            $table->unsignedBiginteger('created_by')->unsigned();
            $table->foreign('created_by')->references('id')
                 ->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_milestones');
    }
};