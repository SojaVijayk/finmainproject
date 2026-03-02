<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
// use DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
       Schema::create('timesheet_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });

        // Insert default system categories
        // DB::table('timesheet_categories')->insert([
        //     ['name' => 'Capacity Building', 'is_system' => true],
        //     ['name' => 'Consulting', 'is_system' => true],
        //     ['name' => 'Manpower Services', 'is_system' => true],
        //     ['name' => 'Recruitment', 'is_system' => true],
        //     ['name' => 'Research & Studies', 'is_system' => true],
        //     ['name' => 'Business Development', 'is_system' => true],
        //     ['name' => 'General CMD/Administration', 'is_system' => true],
        // ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timesheet_categories');
    }
};