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
        Schema::create('deduction_masters', function (Blueprint $table) {
            $table->id();
            $table->string('p_id')->nullable();
            $table->boolean('tds')->default(false);
            $table->decimal('tds_value', 15, 2)->nullable();
            $table->boolean('epf')->default(false);
            $table->decimal('epf_value', 15, 2)->nullable();
            $table->boolean('pf')->default(false);
            $table->decimal('pf_value', 15, 2)->nullable();
            $table->boolean('lic')->default(false);
            $table->decimal('lic_value', 15, 2)->nullable();
            $table->boolean('edli')->default(false);
            $table->decimal('edli_value', 15, 2)->nullable();
            $table->boolean('other')->default(false);
            $table->decimal('other_value', 15, 2)->nullable();
            $table->string('other_details')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deduction_masters');
    }
};
