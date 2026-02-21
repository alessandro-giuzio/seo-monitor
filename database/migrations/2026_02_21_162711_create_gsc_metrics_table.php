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
        Schema::create('gsc_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('website_id')->constrained()->cascadeOnDelete();
            $table->date('metric_date');
            $table->string('query')->nullable();
            $table->string('page_url')->nullable();
            $table->unsignedInteger('clicks')->default(0);
            $table->unsignedInteger('impressions')->default(0);
            $table->decimal('ctr', 6, 4)->nullable();
            $table->decimal('avg_position', 8, 2)->unsigned()->nullable();
            $table->timestamps();

            $table->index(['website_id', 'metric_date']);
            $table->index(['website_id', 'page_url']);
            $table->index(['website_id', 'query']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gsc_metrics');
    }
};
