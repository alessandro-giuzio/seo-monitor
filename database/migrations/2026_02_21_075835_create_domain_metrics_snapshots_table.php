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
        Schema::create('domain_metrics_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('website_id')->constrained()->cascadeOnDelete();
            $table->timestamp('snapshot_at');
            $table->unsignedInteger('estimated_traffic')->nullable();
            $table->unsignedInteger('organic_keywords')->nullable();
            $table->unsignedInteger('referring_domains')->nullable();
            $table->unsignedInteger('backlinks_count')->nullable();
            $table->unsignedSmallInteger('visibility_index')->nullable();
            $table->unsignedInteger('avg_position')->nullable();
            $table->timestamps();

            $table->index(['website_id', 'snapshot_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('domain_metrics_snapshots');
    }
};
