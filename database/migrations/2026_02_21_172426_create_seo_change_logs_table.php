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
        Schema::create('seo_change_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('website_id')->constrained()->cascadeOnDelete();
            $table->timestamp('changed_at');
            $table->string('area');
            $table->string('title');
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->string('impact_level')->default('medium');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['website_id', 'changed_at']);
            $table->index(['website_id', 'area']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seo_change_logs');
    }
};
