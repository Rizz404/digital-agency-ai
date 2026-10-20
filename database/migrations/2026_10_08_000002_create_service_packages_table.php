<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The agency's catalog. Prices live here and nowhere else: the AI Digital Strategist may
     * only quote a price it has just read through a tool call.
     */
    public function up(): void
    {
        Schema::create('service_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained()->cascadeOnDelete();
            $table->string('category'); // branding | web_design | performance_ads | content | seo | automation
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->json('translations')->nullable();
            $table->decimal('price', 12, 2);
            $table->string('image_url')->nullable();
            $table->json('tags')->nullable(); // startup, growth, enterprise, signature, ...
            $table->json('requirements')->nullable(); // brand_brief, brand_assets, analytics_access, ...
            $table->string('delivery')->nullable(); // project | monthly | sprint | retainer | workshop
            $table->unsignedSmallInteger('estimated_days')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_sold_out')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->softDeletes();
            $table->timestamps();
            $table->unique(['agency_id', 'slug']);
            $table->index(['agency_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_packages');
    }
};
