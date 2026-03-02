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
        Schema::create('project_employees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBiginteger('project_id')->unsigned();
            $table->foreign('project_id')->references('id')
                 ->on('projects')->onDelete('cascade');
            $table->string('empId');

            $table->unsignedBiginteger('user_type')->unsigned();
            $table->foreign('user_type')->references('id')
                 ->on('project_user_types')->onDelete('cascade');
            $table->string('prefix');
            $table->string('name');
            $table->string('last_name');
            // $table->smallInteger('gender');
            $table->unsignedBiginteger('gender_id')->unsigned();
            $table->foreign('gender_id')->references('id')
                 ->on('gender')->onDelete('cascade');
            $table->string('address');
            $table->smallInteger('country');
            $table->smallInteger('state');
            $table->smallInteger('district');
            $table->string('pincode');
            $table->string('mobile_pri');
            $table->string('mobile_sec');
            $table->string('email_pri');
            $table->string('email_sec');
            $table->string('whatsapp');
            $table->date('dob');
            $table->string('pan');
            $table->string('profile_pic');
            $table->smallInteger('status');
            // $table->smallInteger('designation_id');
            $table->unsignedBiginteger('designation_id')->unsigned();
            $table->foreign('designation_id')->references('id')
                 ->on('project_designations')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_employees');
    }
};