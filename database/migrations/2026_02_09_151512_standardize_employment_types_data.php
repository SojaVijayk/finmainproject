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
        DB::table('service')->where('employment_type', 'daily wages')->update(['employment_type' => 'Daily Wages']);
        DB::table('service')->where('employment_type', 'full time')->update(['employment_type' => 'Full Time']);
        DB::table('service')->where('employment_type', 'Regular')->update(['employment_type' => 'Full Time']);
        DB::table('service')->where('employment_type', 'interns')->update(['employment_type' => 'Interns']);
        DB::table('service')->where('employment_type', 'contract')->update(['employment_type' => 'Contract']);
        DB::table('service')->where('employment_type', 'parttime')->update(['employment_type' => 'Part Time']);
        DB::table('service')->where('employment_type', 'freelance')->update(['employment_type' => 'Freelance']);
        DB::table('service')->where('employment_type', 'temporary')->update(['employment_type' => 'Temporary']);
        DB::table('service')->where('employment_type', 'appretice')->update(['employment_type' => 'Apprentice']);
        DB::table('service')->where('employment_type', 'Permanent')->update(['employment_type' => 'Permanent']); // Already standard if exists
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No easy way to reverse specific past values without keeping track
    }
};
