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
        Schema::create('release_qa_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('website_id')->constrained()->cascadeOnDelete();
            $table->timestamp('checked_at');
            $table->string('environment')->default('production');
            $table->string('release_tag')->nullable();
            $table->string('status')->default('warn');
            $table->unsignedTinyInteger('score')->default(0);
            $table->json('summary')->nullable();
            $table->timestamps();

            $table->index(['website_id', 'checked_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('release_qa_runs');
    }
};
