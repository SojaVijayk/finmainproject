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
         Schema::create('requirements', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('type_id')->comment('1:requirement, 2:direct_proposal');
            $table->string('temp_no')->unique();
            $table->foreignId('project_category_id')->constrained('project_categories');
            $table->foreignId('project_subcategory_id')->constrained('project_subcategories');
            $table->text('project_title');
            $table->foreignId('client_id')->constrained('clients');
            $table->foreignId('client_contact_person_id')->constrained('client_contact_people');
            $table->string('ref_no')->nullable();
            $table->foreignId('allocated_to')->nullable()->constrained('users');
            $table->timestamp('allocated_at')->nullable();
            $table->foreignId('allocated_by')->nullable()->constrained('users');
            $table->unsignedTinyInteger('status')->default(0)->comment('0:initiated,1:send to director for approval,2:approved by director,3:rejected,4:send to PAC');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requirements');
    }
};
