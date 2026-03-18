<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pay_items', function (Blueprint $table) {
            $table->enum('calculation_type', ['slab', 'fixed', 'percentage'])->default('slab')->after('is_slab_based');
            $table->decimal('calculation_value', 10, 2)->nullable()->after('calculation_type');
        });
    }

    public function down(): void
    {
        Schema::table('pay_items', function (Blueprint $table) {
            $table->dropColumn(['calculation_type', 'calculation_value']);
        });
    }
};
