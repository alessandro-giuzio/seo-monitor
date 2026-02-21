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
        Schema::create('keywords', function (Blueprint $table) {
            $table->id();
            $table->foreignId('website_id')->constrained()->cascadeOnDelete();
            $table->string('term');
            $table->string('target_url')->nullable();
            $table->string('search_engine')->default('Google');
            $table->string('location')->nullable();
            $table->string('device')->default('desktop');
            $table->unsignedTinyInteger('priority')->default(2);
            $table->timestamps();

            $table->index(['website_id', 'term']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('keywords');
    }
};
