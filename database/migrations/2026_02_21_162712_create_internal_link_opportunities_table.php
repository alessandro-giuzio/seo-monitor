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
        Schema::create('internal_link_opportunities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crawl_run_id')->constrained()->cascadeOnDelete();
            $table->foreignId('website_id')->constrained()->cascadeOnDelete();
            $table->string('source_url');
            $table->string('target_url');
            $table->unsignedTinyInteger('priority_score')->default(0);
            $table->string('reason');
            $table->timestamps();

            $table->index(['website_id', 'priority_score']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('internal_link_opportunities');
    }
};
