<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class CrawlPage extends Model
{
    use HasFactory;

    protected $fillable = [
        'crawl_run_id',
        'website_id',
        'url',
        'status_code',
        'title',
        'canonical',
        'meta_robots',
        'h1_count',
        'word_count',
        'internal_outlinks',
        'internal_inlinks',
        'url_depth',
        'html_lang',
        'hreflang_count',
        'charset',
        'has_amp',
        'is_indexable',
        'is_in_sitemap',
        'is_orphan',
        'issues',
        'last_crawled_at',
    ];

    protected function casts(): array
    {
        return [
            'is_indexable' => 'boolean',
            'is_in_sitemap' => 'boolean',
            'is_orphan' => 'boolean',
            'has_amp' => 'boolean',
            'issues' => 'array',
            'last_crawled_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<CrawlRun, $this>
     */
    public function crawlRun(): BelongsTo
    {
        return $this->belongsTo(CrawlRun::class);
    }

    /**
     * @return BelongsTo<Website, $this>
     */
    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }
}
