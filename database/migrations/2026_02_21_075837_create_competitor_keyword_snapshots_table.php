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
        Schema::create('competitor_keyword_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competitor_id')->constrained()->cascadeOnDelete();
            $table->string('keyword');
            $table->timestamp('checked_at');
            $table->unsignedSmallInteger('position')->nullable();
            $table->unsignedInteger('search_volume')->nullable();
            $table->timestamps();

            $table->index(['competitor_id', 'keyword']);
            $table->index(['competitor_id', 'checked_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('competitor_keyword_snapshots');
    }
};
