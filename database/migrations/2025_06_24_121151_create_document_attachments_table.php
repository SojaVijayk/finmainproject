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
        Schema::create('document_attachments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('document_id')->constrained();
    $table->string('file_path');
    $table->string('original_name');
    $table->string('mime_type');
    $table->integer('size');
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_attachments');
    }
};
