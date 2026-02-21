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
        Schema::table('crawl_pages', function (Blueprint $table) {
            $table->unsignedTinyInteger('url_depth')->default(0)->after('internal_inlinks');
            $table->string('html_lang', 16)->nullable()->after('url_depth');
            $table->unsignedSmallInteger('hreflang_count')->default(0)->after('html_lang');
            $table->string('charset', 32)->nullable()->after('hreflang_count');
            $table->boolean('has_amp')->default(false)->after('charset');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('crawl_pages', function (Blueprint $table) {
            $table->dropColumn([
                'url_depth',
                'html_lang',
                'hreflang_count',
                'charset',
                'has_amp',
            ]);
        });
    }
};
