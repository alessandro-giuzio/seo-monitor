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
        Schema::table('websites', function (Blueprint $table) {
            $table->string('gsc_property')->nullable()->after('base_url');
            $table->string('alert_email')->nullable()->after('target_country');
            $table->unsignedSmallInteger('crawl_frequency_hours')->default(24)->after('alert_email');
            $table->timestamp('next_crawl_at')->nullable()->after('crawl_frequency_hours');
            $table->timestamp('last_crawl_at')->nullable()->after('next_crawl_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('websites', function (Blueprint $table) {
            $table->dropColumn([
                'gsc_property',
                'alert_email',
                'crawl_frequency_hours',
                'next_crawl_at',
                'last_crawl_at',
            ]);
        });
    }
};
