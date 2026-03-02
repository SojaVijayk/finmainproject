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
            if (!Schema::hasColumn('proposals', 'project_status')) {
                $table->unsignedTinyInteger('project_status')->default(0)->after('status')->comment('0:no project, 1:project created');
            }
        });

        Schema::table('requirements', function (Blueprint $table) {
            if (!Schema::hasColumn('requirements', 'proposal_status')) {
                $table->unsignedTinyInteger('proposal_status')->default(0)->after('status')->comment('0:no proposal, 1:proposal created');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proposals', function (Blueprint $table) {
            $table->dropColumn('project_status');
        });

        Schema::table('requirements', function (Blueprint $table) {
            $table->dropColumn('proposal_status');
        });
    }
};
