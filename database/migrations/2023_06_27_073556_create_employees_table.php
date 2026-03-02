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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            // $table->integer('user_id')->unsigned()->nullable();
            // $table->foreign('user_id')->references('id')->on('users');

            $table->unsignedBiginteger('user_id')->unsigned();
            $table->foreign('user_id')->references('id')
                 ->on('users')->onDelete('cascade');
                 $table->string('empId');

            //$table->foreign('user_id')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->string('prefix');
            $table->smallInteger('gender');
            $table->string('address');
            $table->smallInteger('country');
            $table->smallInteger('state');
            $table->smallInteger('district');
            $table->string('pincode');
            $table->string('mobile');
            $table->string('mobile_sec');
            $table->string('email');
            $table->string('email_sec');
            $table->string('whatsapp');
            $table->string('facebook');
            $table->string('twitter');
            $table->string('linkedin');
            $table->string('instagram');
            $table->date('dob');
            $table->string('pan');
            $table->string('profile_pic');
            $table->smallInteger('status');
            $table->smallInteger('designation');
            $table->smallInteger('employment_type');
            $table->smallInteger('reporting_officer');
            $table->string('contract_duration');
            $table->string('contract_start_date');
            $table->string('contract_end_date');



            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};