<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultations', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 20)->unique();
            $table->foreignId('agency_id')->constrained()->cascadeOnDelete();
            $table->foreignId('consultant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('conversation_id')->nullable()->constrained()->nullOnDelete();
            $table->string('consultant_name');
            $table->unsignedSmallInteger('duration_minutes')->default(60);
            $table->string('requester_name');
            $table->string('requester_email')->nullable();
            $table->string('requester_phone')->nullable();
            $table->string('contact_type', 20)->nullable(); // whatsapp | phone | email
            $table->date('consultation_date');
            $table->string('time_slot', 5);
            $table->unsignedTinyInteger('companies')->default(1);
            $table->string('occasion', 30)->nullable();
            $table->decimal('consultation_total', 12, 2)->default(0);
            $table->string('status')->default('pending'); // pending | confirmed | cancelled
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultations');
    }
};
