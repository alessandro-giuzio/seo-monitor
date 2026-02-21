<?php

namespace App\Services;

use App\Models\CrawlRun;
use App\Models\Website;
use Illuminate\Support\Facades\Http;

class SeoCrawlerService
{
    public function runForWebsite(Website $website, int $maxPages = 30): CrawlRun
    {
        $run = CrawlRun::create([
            'website_id' => $website->id,
            'started_at' => now(),
            'status' => 'running',
            'pages_crawled' => 0,
        ]);

        $baseUrl = rtrim($website->base_url, '/');
        $baseHost = (string) parse_url($baseUrl, PHP_URL_HOST);
        $robotsUrl = $baseUrl.'/robots.txt';
        $sitemapUrl = $baseUrl.'/sitemap.xml';

        $robotsBody = '';
        $disallowed = [];
        $robotsResponse = Http::timeout(15)->get($robotsUrl);
        if ($robotsResponse->successful()) {
            $robotsBody = $robotsResponse->body();
            $disallowed = $this->extractDisallowedPaths($robotsBody);
        }

        $sitemapUrls = $this->extractSitemapUrls($sitemapUrl);
        if ($sitemapUrls === []) {
            $sitemapUrls = [$baseUrl];
        }

        $urlSet = [];
        foreach ($sitemapUrls as $url) {
            if (count($urlSet) >= $maxPages) {
                break;
            }

            if (! str_starts_with($url, 'http')) {
                continue;
            }

            $host = (string) parse_url($url, PHP_URL_HOST);
            if ($host !== $baseHost) {
                continue;
            }

            $urlSet[$url] = true;
        }

        if (! isset($urlSet[$baseUrl])) {
            $urlSet[$baseUrl] = true;
        }

        $urlList = array_keys($urlSet);
        $pageRows = [];
        $inlinks = [];

        foreach ($urlList as $url) {
            $response = Http::timeout(20)
                ->withHeaders(['User-Agent' => 'SEO Toolkit Crawler/1.0'])
                ->get($url);

            $statusCode = $response->status();
            $html = $response->successful() ? $response->body() : '';

            $analysis = $this->analyzePage($url, $html, $baseHost, $disallowed);
            $inlinks[$url] = $inlinks[$url] ?? 0;
            foreach ($analysis['internal_links'] as $target) {
                if (! isset($inlinks[$target])) {
                    $inlinks[$target] = 0;
                }
                $inlinks[$target]++;
            }

            $pageRows[$url] = [
                'crawl_run_id' => $run->id,
                'website_id' => $website->id,
                'url' => $url,
                'status_code' => $statusCode,
                'title' => $analysis['title'],
                'canonical' => $analysis['canonical'],
                'meta_robots' => $analysis['meta_robots'],
                'h1_count' => $analysis['h1_count'],
                'word_count' => $analysis['word_count'],
                'internal_outlinks' => count($analysis['internal_links']),
                'internal_inlinks' => 0,
                'url_depth' => $analysis['url_depth'],
                'html_lang' => $analysis['html_lang'],
                'hreflang_count' => $analysis['hreflang_count'],
                'charset' => $analysis['charset'],
                'has_amp' => $analysis['has_amp'],
                'is_indexable' => false,
                'is_in_sitemap' => true,
                'is_orphan' => false,
                'issues' => $analysis['issues'],
                'last_crawled_at' => now(),
            ];
        }

        foreach ($pageRows as $url => &$row) {
            $inlinkCount = $inlinks[$url] ?? 0;
            $isHome = rtrim($url, '/') === $baseUrl;
            $isNoindex = str_contains(strtolower((string) ($row['meta_robots'] ?? '')), 'noindex');
            $canonicalHost = $row['canonical'] ? parse_url($row['canonical'], PHP_URL_HOST) : null;
            $isCanonicalExternal = $canonicalHost && $canonicalHost !== $baseHost;

            $row['internal_inlinks'] = $inlinkCount;
            $row['is_orphan'] = ! $isHome && $inlinkCount === 0;
            $row['is_indexable'] = ($row['status_code'] === 200) && ! $isNoindex && ! $isCanonicalExternal;
        }
        unset($row);

        $run->pages()->createMany(array_values($pageRows));

        $this->generateInternalLinkOpportunities($website, $run);

        $run->update([
            'finished_at' => now(),
            'status' => 'completed',
            'pages_crawled' => count($pageRows),
            'summary' => [
                'robots_url' => $robotsUrl,
                'robots_found' => $robotsResponse->successful(),
                'sitemap_url' => $sitemapUrl,
                'sitemap_urls' => count($urlList),
                'indexable_pages' => $run->pages()->where('is_indexable', true)->count(),
                'orphan_pages' => $run->pages()->where('is_orphan', true)->count(),
                'max_url_depth' => $run->pages()->max('url_depth'),
            ],
        ]);

        $website->update([
            'last_crawl_at' => now(),
            'next_crawl_at' => now()->addHours(max(1, (int) $website->crawl_frequency_hours)),
        ]);

        return $run->fresh(['pages', 'linkOpportunities']);
    }

    /**
     * @return array<int, string>
     */
    private function extractDisallowedPaths(string $robotsBody): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $robotsBody) ?: [];
        $disallowed = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            if (str_starts_with(strtolower($line), 'disallow:')) {
                $path = trim(substr($line, 9));
                if ($path !== '') {
                    $disallowed[] = $path;
                }
            }
        }

        return $disallowed;
    }

    /**
     * @return array<int, string>
     */
    private function extractSitemapUrls(string $sitemapUrl): array
    {
        $response = Http::timeout(15)->get($sitemapUrl);
        if (! $response->successful()) {
            return [];
        }

        $xml = @simplexml_load_string($response->body());
        if (! $xml) {
            return [];
        }

        $urls = [];

        if (isset($xml->url)) {
            foreach ($xml->url as $urlNode) {
                if (isset($urlNode->loc)) {
                    $urls[] = trim((string) $urlNode->loc);
                }
            }
        }

        if ($urls === [] && isset($xml->sitemap)) {
            foreach ($xml->sitemap as $sitemapNode) {
                if (! isset($sitemapNode->loc)) {
                    continue;
                }

                $childUrls = $this->extractSitemapUrls(trim((string) $sitemapNode->loc));
                foreach ($childUrls as $childUrl) {
                    $urls[] = $childUrl;
                }
            }
        }

        return array_values(array_unique($urls));
    }

    /**
     * @param array<int, string> $disallowed
     * @return array{title:?string, canonical:?string, meta_robots:?string, h1_count:int, word_count:int, internal_links:array<int, string>, issues:array<int, string>, url_depth:int, html_lang:?string, hreflang_count:int, charset:?string, has_amp:bool}
     */
    private function analyzePage(string $url, string $html, string $baseHost, array $disallowed): array
    {
        if ($html === '') {
            return [
                'title' => null,
                'canonical' => null,
                'meta_robots' => null,
                'h1_count' => 0,
                'word_count' => 0,
                'internal_links' => [],
                'issues' => ['Page could not be fetched or returned empty body.'],
                'url_depth' => $this->getUrlDepth($url),
                'html_lang' => null,
                'hreflang_count' => 0,
                'charset' => null,
                'has_amp' => false,
            ];
        }

        $baseScheme = (string) (parse_url($url, PHP_URL_SCHEME) ?: 'https');

        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $dom->loadHTML($html);
        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);

        $title = null;
        $titleNode = $xpath->query('//title')->item(0);
        if ($titleNode) {
            $title = trim($titleNode->textContent);
        }

        $canonical = null;
        $canonicalNode = $xpath->query('//link[@rel="canonical"]')->item(0);
        if ($canonicalNode) {
            $canonical = trim((string) $canonicalNode->attributes?->getNamedItem('href')?->nodeValue);
        }

        $metaRobots = null;
        $metaNode = $xpath->query('//meta[@name="robots"]')->item(0);
        if ($metaNode) {
            $metaRobots = trim((string) $metaNode->attributes?->getNamedItem('content')?->nodeValue);
        }

        $htmlLang = null;
        $htmlNode = $xpath->query('//html')->item(0);
        if ($htmlNode) {
            $htmlLang = trim((string) $htmlNode->attributes?->getNamedItem('lang')?->nodeValue) ?: null;
        }

        $charset = null;
        $charsetNode = $xpath->query('//meta[@charset]')->item(0);
        if ($charsetNode) {
            $charset = trim((string) $charsetNode->attributes?->getNamedItem('charset')?->nodeValue) ?: null;
        }
        if (! $charset) {
            $contentTypeNode = $xpath->query('//meta[translate(@http-equiv, "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz")="content-type"]')->item(0);
            if ($contentTypeNode) {
                $contentType = strtolower((string) $contentTypeNode->attributes?->getNamedItem('content')?->nodeValue);
                if (str_contains($contentType, 'charset=')) {
                    $charset = trim((string) substr($contentType, strpos($contentType, 'charset=') + 8));
                }
            }
        }

        $hreflangCount = $xpath->query('//link[@rel="alternate" and @hreflang]')?->length ?? 0;
        $hasAmp = ($xpath->query('//link[@rel="amphtml"]')?->length ?? 0) > 0 || str_contains(strtolower($url), '/amp');

        $h1Count = $xpath->query('//h1')?->length ?? 0;
        $wordCount = str_word_count(strip_tags($html));

        $internalLinks = [];
        $anchorNodes = $xpath->query('//a[@href]');
        if ($anchorNodes) {
            foreach ($anchorNodes as $node) {
                $href = trim((string) $node->attributes?->getNamedItem('href')?->nodeValue);
                if ($href === '' || str_starts_with($href, '#') || str_starts_with($href, 'mailto:') || str_starts_with($href, 'tel:')) {
                    continue;
                }

                if (str_starts_with($href, '/')) {
                    $normalized = $baseScheme.'://'.$baseHost.$href;
                } else {
                    $normalized = $href;
                }

                $host = parse_url($normalized, PHP_URL_HOST);
                if ($host === $baseHost) {
                    $internalLinks[] = rtrim($normalized, '/');
                }
            }
        }

        $issues = [];
        if (! $title) {
            $issues[] = 'Missing title';
        }
        if ($h1Count === 0) {
            $issues[] = 'Missing H1';
        }
        if ($wordCount < 250) {
            $issues[] = 'Thin content';
        }

        $path = (string) parse_url($url, PHP_URL_PATH);
        foreach ($disallowed as $disallowPath) {
            if ($disallowPath !== '/' && str_starts_with($path, $disallowPath)) {
                $issues[] = 'Blocked by robots.txt rule '.$disallowPath;
                break;
            }
        }

        return [
            'title' => $title,
            'canonical' => $canonical,
            'meta_robots' => $metaRobots,
            'h1_count' => $h1Count,
            'word_count' => $wordCount,
            'internal_links' => array_values(array_unique($internalLinks)),
            'issues' => $issues,
            'url_depth' => $this->getUrlDepth($url),
            'html_lang' => $htmlLang,
            'hreflang_count' => $hreflangCount,
            'charset' => $charset,
            'has_amp' => $hasAmp,
        ];
    }

    private function getUrlDepth(string $url): int
    {
        $path = trim((string) parse_url($url, PHP_URL_PATH), '/');
        if ($path === '') {
            return 0;
        }

        return count(array_filter(explode('/', $path)));
    }

    private function generateInternalLinkOpportunities(Website $website, CrawlRun $run): void
    {
        $run->linkOpportunities()->delete();

        $targets = $run->pages()
            ->where('is_indexable', true)
            ->where('internal_inlinks', '<=', 1)
            ->where('word_count', '>=', 300)
            ->orderByDesc('word_count')
            ->limit(10)
            ->get();

        $sources = $run->pages()
            ->where('is_indexable', true)
            ->where('internal_outlinks', '<=', 80)
            ->orderByDesc('word_count')
            ->limit(25)
            ->get();

        foreach ($targets as $target) {
            $source = $sources->first(fn ($candidate) => $candidate->url !== $target->url);
            if (! $source) {
                continue;
            }

            $score = min(100, (int) (70 + ($target->word_count / 100)));
            $run->linkOpportunities()->create([
                'website_id' => $website->id,
                'source_url' => $source->url,
                'target_url' => $target->url,
                'priority_score' => $score,
                'reason' => 'Target page has low internal inlinks and strong content depth.',
            ]);
        }
    }
}
