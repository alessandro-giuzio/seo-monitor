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
        Schema::create('backlinks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('website_id')->constrained()->cascadeOnDelete();
            $table->string('source_url');
            $table->string('target_url');
            $table->string('anchor_text')->nullable();
            $table->unsignedTinyInteger('source_authority')->nullable();
            $table->boolean('is_nofollow')->default(false);
            $table->boolean('is_toxic')->default(false);
            $table->timestamp('found_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();

            $table->index(['website_id', 'source_authority']);
            $table->index(['website_id', 'is_toxic']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('backlinks');
    }
};
