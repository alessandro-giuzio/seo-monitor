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
        Schema::create('seo_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('website_id')->nullable()->constrained()->nullOnDelete();
            $table->string('url');
            $table->timestamp('audited_at');
            $table->string('status')->default('warn');
            $table->unsignedTinyInteger('score')->default(0);
            $table->string('title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('canonical')->nullable();
            $table->unsignedSmallInteger('h1_count')->default(0);
            $table->unsignedSmallInteger('image_without_alt')->default(0);
            $table->unsignedSmallInteger('internal_links')->default(0);
            $table->unsignedSmallInteger('external_links')->default(0);
            $table->unsignedInteger('word_count')->default(0);
            $table->json('issues')->nullable();
            $table->timestamps();

            $table->index(['website_id', 'audited_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seo_audits');
    }
};
