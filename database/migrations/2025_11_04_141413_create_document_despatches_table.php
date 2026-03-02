<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('document_despatches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->onDelete('cascade');
            $table->date('despatch_date');
            $table->date('actual_despatch_date')->nullable();
            $table->string('type'); // e.g. by_hand, mail, speed_post, etc.
            $table->string('mail_id')->nullable(); // only if type=mail
            $table->string('send_by')->nullable();
            $table->string('tracking_number')->nullable(); // for speed/registered posts
            $table->string('acknowledgement_file')->nullable(); // optional file
            $table->string('despatch_receipt')->nullable(); // optional file
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_despatches');
    }
};