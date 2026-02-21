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
        Schema::create('crawl_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crawl_run_id')->constrained()->cascadeOnDelete();
            $table->foreignId('website_id')->constrained()->cascadeOnDelete();
            $table->string('url');
            $table->unsignedSmallInteger('status_code')->nullable();
            $table->string('title')->nullable();
            $table->string('canonical')->nullable();
            $table->string('meta_robots')->nullable();
            $table->unsignedSmallInteger('h1_count')->default(0);
            $table->unsignedInteger('word_count')->default(0);
            $table->unsignedSmallInteger('internal_outlinks')->default(0);
            $table->unsignedSmallInteger('internal_inlinks')->default(0);
            $table->boolean('is_indexable')->default(false);
            $table->boolean('is_in_sitemap')->default(false);
            $table->boolean('is_orphan')->default(false);
            $table->json('issues')->nullable();
            $table->timestamp('last_crawled_at');
            $table->timestamps();

            $table->index(['website_id', 'is_indexable']);
            $table->index(['website_id', 'is_orphan']);
            $table->index(['website_id', 'url']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crawl_pages');
    }
};
