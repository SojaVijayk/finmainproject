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
        Schema::table('proposals', function (Blueprint $table) {
            $table->string('client_status')->nullable()->comment('accepted,rejected,resubmit_requested');
            $table->text('client_comments')->nullable();
            $table->dateTime('client_status_updated_at')->nullable();
            $table->foreignId('client_status_updated_by')->nullable()->constrained('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proposals', function (Blueprint $table) {
            $table->dropColumn(['client_status', 'client_comments', 'client_status_updated_at', 'client_status_updated_by']);
        });
    }
};