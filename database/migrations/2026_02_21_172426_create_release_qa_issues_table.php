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
        Schema::create('release_qa_issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('release_qa_run_id')->constrained()->cascadeOnDelete();
            $table->foreignId('website_id')->constrained()->cascadeOnDelete();
            $table->string('category');
            $table->string('severity')->default('medium');
            $table->string('title');
            $table->text('details')->nullable();
            $table->string('url')->nullable();
            $table->timestamps();

            $table->index(['release_qa_run_id', 'severity']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('release_qa_issues');
    }
};
