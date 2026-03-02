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
        Schema::table('project_employee', function (Blueprint $table) {
            $table->string('bank_name')->nullable()->after('mobile');
            $table->string('account_no')->nullable()->after('bank_name');
            $table->string('account_name')->nullable()->after('account_no');
            $table->string('branch')->nullable()->after('account_name');
            $table->string('ifsc_code')->nullable()->after('branch');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_employee', function (Blueprint $table) {
            $table->dropColumn(['bank_name', 'account_no', 'account_name', 'branch', 'ifsc_code']);
        });
    }
};
