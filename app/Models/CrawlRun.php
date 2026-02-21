<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class CrawlRun extends Model
{
    use HasFactory;

    protected $fillable = [
        'website_id',
        'started_at',
        'finished_at',
        'status',
        'pages_crawled',
        'summary',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'summary' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Website, $this>
     */
    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    /**
     * @return HasMany<CrawlPage, $this>
     */
    public function pages(): HasMany
    {
        return $this->hasMany(CrawlPage::class);
    }

    /**
     * @return HasMany<InternalLinkOpportunity, $this>
     */
    public function linkOpportunities(): HasMany
    {
        return $this->hasMany(InternalLinkOpportunity::class);
    }
}
