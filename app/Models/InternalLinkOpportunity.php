<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class InternalLinkOpportunity extends Model
{
    use HasFactory;

    protected $fillable = [
        'crawl_run_id',
        'website_id',
        'source_url',
        'target_url',
        'priority_score',
        'reason',
    ];

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
