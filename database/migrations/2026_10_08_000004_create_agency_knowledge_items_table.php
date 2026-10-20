<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The approved agency knowledge base the AI retrieves from. No record here,
     * no answer from the AI: static and semi-static facts only, never catalog
     * prices or consultation availability (see service_packages and consultation_slots).
     */
    public function up(): void
    {
        Schema::create('agency_knowledge_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained()->cascadeOnDelete();
            $table->string('category');
            $table->string('title');
            $table->text('body');
            $table->json('translations')->nullable();
            $table->json('tags')->nullable();
            $table->string('image_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['agency_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agency_knowledge_items');
    }
};
