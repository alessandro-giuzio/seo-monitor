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
        Schema::table('ranking_snapshots', function (Blueprint $table) {
            // 'manual' (logged on the website page) or 'gsc' (derived from Search Console data).
            $table->string('source', 20)->default('manual')->after('keyword_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ranking_snapshots', function (Blueprint $table) {
            $table->dropColumn('source');
        });
    }
};
