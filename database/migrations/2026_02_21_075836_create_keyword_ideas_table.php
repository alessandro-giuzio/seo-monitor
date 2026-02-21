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
        Schema::create('keyword_ideas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('website_id')->nullable()->constrained()->nullOnDelete();
            $table->string('seed_keyword')->nullable();
            $table->string('keyword');
            $table->unsignedInteger('search_volume')->nullable();
            $table->unsignedTinyInteger('keyword_difficulty')->nullable();
            $table->decimal('cpc', 8, 2)->unsigned()->nullable();
            $table->string('intent')->nullable();
            $table->string('country', 2)->nullable();
            $table->timestamps();

            $table->index(['website_id', 'keyword']);
            $table->index(['country', 'search_volume']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('keyword_ideas');
    }
};
