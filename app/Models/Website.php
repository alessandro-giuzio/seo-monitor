<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Model;

class Website extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'base_url',
        'gsc_property',
        'industry',
        'target_country',
        'alert_email',
        'crawl_frequency_hours',
        'next_crawl_at',
        'last_crawl_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'next_crawl_at' => 'datetime',
            'last_crawl_at' => 'datetime',
        ];
    }

    /**
     * @return HasMany<Keyword, $this>
     */
    public function keywords(): HasMany
    {
        return $this->hasMany(Keyword::class);
    }

    /**
     * @return HasMany<UptimeCheck, $this>
     */
    public function uptimeChecks(): HasMany
    {
        return $this->hasMany(UptimeCheck::class);
    }

    /**
     * @return HasMany<SeoAudit, $this>
     */
    public function seoAudits(): HasMany
    {
        return $this->hasMany(SeoAudit::class);
    }

    /**
     * @return HasMany<DomainMetricsSnapshot, $this>
     */
    public function domainMetricsSnapshots(): HasMany
    {
        return $this->hasMany(DomainMetricsSnapshot::class)->orderByDesc('snapshot_at');
    }

    /**
     * @return HasMany<Competitor, $this>
     */
    public function competitors(): HasMany
    {
        return $this->hasMany(Competitor::class);
    }

    /**
     * @return HasMany<KeywordIdea, $this>
     */
    public function keywordIdeas(): HasMany
    {
        return $this->hasMany(KeywordIdea::class);
    }

    /**
     * @return HasMany<Backlink, $this>
     */
    public function backlinks(): HasMany
    {
        return $this->hasMany(Backlink::class);
    }

    /**
     * @return HasMany<GscMetric, $this>
     */
    public function gscMetrics(): HasMany
    {
        return $this->hasMany(GscMetric::class)->orderByDesc('metric_date');
    }

    /**
     * @return HasMany<CrawlRun, $this>
     */
    public function crawlRuns(): HasMany
    {
        return $this->hasMany(CrawlRun::class)->orderByDesc('started_at');
    }

    /**
     * @return HasMany<CrawlPage, $this>
     */
    public function crawlPages(): HasMany
    {
        return $this->hasMany(CrawlPage::class)->orderByDesc('last_crawled_at');
    }

    /**
     * @return HasMany<InternalLinkOpportunity, $this>
     */
    public function internalLinkOpportunities(): HasMany
    {
        return $this->hasMany(InternalLinkOpportunity::class)->orderByDesc('priority_score');
    }

    /**
     * @return HasMany<SeoAlert, $this>
     */
    public function seoAlerts(): HasMany
    {
        return $this->hasMany(SeoAlert::class)->orderByDesc('detected_at');
    }

    /**
     * @return HasMany<SeoTask, $this>
     */
    public function seoTasks(): HasMany
    {
        return $this->hasMany(SeoTask::class)->orderBy('status')->orderByDesc('created_at');
    }

    /**
     * @return HasOne<UptimeCheck, $this>
     */
    public function latestUptimeCheck(): HasOne
    {
        return $this->hasOne(UptimeCheck::class)->latestOfMany('checked_at');
    }

    /**
     * @return HasOne<SeoAudit, $this>
     */
    public function latestSeoAudit(): HasOne
    {
        return $this->hasOne(SeoAudit::class)->latestOfMany('audited_at');
    }
}
