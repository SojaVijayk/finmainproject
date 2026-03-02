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
        Schema::create('timesheet_items', function (Blueprint $table) {
        $table->id();
        $table->foreignId('timesheet_id')->constrained()->onDelete('cascade');
        $table->string('item_name');
        $table->decimal('hours', 5, 2)->default(0);
        $table->text('description')->nullable();
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timesheet_items');
    }
};
