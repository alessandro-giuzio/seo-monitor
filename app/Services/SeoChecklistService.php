<?php

namespace App\Services;

use App\Models\Website;

class SeoChecklistService
{
    public function buildForWebsite(Website $website): array
    {
        $latestRun = $website->crawlRuns()->latest('started_at')->first();
        $latestAudit = $website->seoAudits()->latest('audited_at')->first();
        $pages = $latestRun ? $latestRun->pages()->get() : collect();

        $brokenPages = $pages->where('status_code', '>=', 400)->count();
        $maxDepth = (int) ($pages->max('url_depth') ?? 0);
        $missingCanonical = $pages->filter(fn ($p) => blank($p->canonical))->count();
        $noindexPages = $pages->filter(fn ($p) => str_contains(strtolower((string) $p->meta_robots), 'noindex'))->count();
        $thinPages = $pages->where('word_count', '<', 250)->count();
        $hasCwvData = $website->gscMetrics()->exists();
        $withHreflang = $pages->where('hreflang_count', '>', 0)->count();
        $withCharset = $pages->filter(fn ($p) => filled($p->charset))->count();
        $withAmp = $pages->where('has_amp', true)->count();

        return [
            'crawlability' => [
                $this->item('Create a robots.txt file for your site', $latestRun && ($latestRun->summary['robots_found'] ?? false) ? 'pass' : 'fail', $latestRun ? 'robots.txt checked during latest crawl.' : 'Run technical crawl first.'),
                $this->item('Test robots.txt can be crawled properly', $latestRun && ($latestRun->pages_crawled ?? 0) > 0 ? 'pass' : 'warn', $latestRun ? "{$latestRun->pages_crawled} pages crawled." : 'No crawl data.'),
                $this->item('Fix any broken links', $brokenPages === 0 ? 'pass' : 'fail', "{$brokenPages} crawled page(s) returned 4xx/5xx."),
                $this->item('Keep pages within three clicks deep', $maxDepth <= 3 ? 'pass' : 'warn', "Max discovered depth: {$maxDepth}."),
                $this->item('Ensure Google-friendly redirects', $brokenPages === 0 ? 'pass' : 'warn', 'Review redirect chains and broken responses in technical crawl.'),
                $this->item('Use canonical tags where appropriate', $missingCanonical === 0 ? 'pass' : 'warn', "{$missingCanonical} page(s) missing canonical."),
                $this->item('Submit sitemap to Google Search Console', filled($website->gsc_property) && $latestRun ? 'pass' : 'warn', filled($website->gsc_property) ? "Property: {$website->gsc_property}" : 'Set GSC property on website settings.'),
            ],
            'onpage' => [
                $this->item('Optimize title tags and H1 tags', $latestAudit && filled($latestAudit->title) && $latestAudit->h1_count === 1 ? 'pass' : 'warn', $latestAudit ? "Latest audit score {$latestAudit->score}." : 'No audit data.'),
                $this->item('Consolidate duplicate content', 'warn', 'Manual review needed: duplicate cluster detection not automated yet.'),
                $this->item('Add more text on low-content pages', $thinPages === 0 ? 'pass' : 'warn', "{$thinPages} page(s) below 250 words in latest crawl."),
                $this->item('Check SERP title/description rewrites', 'warn', 'Track with GSC query/page diffs and SERP snapshots.'),
                $this->item('Add relevant alt text to images', $latestAudit && $latestAudit->image_without_alt === 0 ? 'pass' : 'fail', $latestAudit ? "{$latestAudit->image_without_alt} image(s) missing alt in latest audit." : 'No audit data.'),
                $this->item('Ensure relevant file/page names', $maxDepth <= 4 ? 'pass' : 'warn', 'Review deep or noisy URL structures in crawl output.'),
            ],
            'technical' => [
                $this->item('Remove unnecessary code', 'warn', 'Requires performance profiler integration.'),
                $this->item('Minify heavy code for speed', 'warn', 'Requires asset-size and performance budget checks.'),
                $this->item('Compress your images', 'warn', 'Requires image weight crawler check.'),
                $this->item('Implement AMP/mobile-friendly pages', $withAmp > 0 ? 'pass' : 'warn', $withAmp > 0 ? "{$withAmp} page(s) with AMP signal." : 'No AMP signal detected in latest crawl.'),
                $this->item('Monitor Core Web Vitals', $hasCwvData ? 'pass' : 'warn', $hasCwvData ? 'GSC data exists for trend reporting.' : 'Import GSC rows to start trend tracking.'),
            ],
            'international' => [
                $this->item('Add proper hreflang tags', $withHreflang > 0 ? 'pass' : 'warn', "{$withHreflang} page(s) include hreflang alternates."),
                $this->item('Declare character encoding', $withCharset === $pages->count() && $pages->isNotEmpty() ? 'pass' : 'warn', "{$withCharset}/{$pages->count()} page(s) expose charset meta."),
                $this->item('Provide language selector if needed', 'warn', 'Manual UX check recommended for multilingual sites.'),
                $this->item('Validate language/country code setup', filled($website->target_country) && $pages->whereNotNull('html_lang')->count() > 0 ? 'pass' : 'warn', filled($website->target_country) ? "Target country: {$website->target_country}" : 'Set target country in website settings.'),
            ],
            'meta' => [
                'latest_run_id' => $latestRun?->id,
                'latest_audit_id' => $latestAudit?->id,
                'pages_scanned' => $pages->count(),
                'noindex_pages' => $noindexPages,
            ],
        ];
    }

    private function item(string $label, string $status, string $detail): array
    {
        return [
            'label' => $label,
            'status' => $status,
            'detail' => $detail,
        ];
    }
}
