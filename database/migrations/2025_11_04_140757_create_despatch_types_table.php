<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('despatch_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('requires_tracking')->default(false);
            $table->boolean('requires_ack')->default(false);
            $table->boolean('requires_mail_id')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('despatch_types');
    }
};