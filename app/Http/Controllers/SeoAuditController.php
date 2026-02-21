<?php

namespace App\Http\Controllers;

use App\Models\SeoAudit;
use App\Models\Website;
use DOMDocument;
use DOMXPath;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class SeoAuditController extends Controller
{
    public function index(): View
    {
        $audits = SeoAudit::query()
            ->with('website')
            ->latest('audited_at')
            ->paginate(25);

        return view('audits.index', ['audits' => $audits]);
    }

    public function show(SeoAudit $audit): View
    {
        $audit->load('website');

        return view('audits.show', ['audit' => $audit]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'website_id' => ['nullable', 'exists:websites,id'],
            'url' => ['required', 'url', 'max:2048'],
            'raw_html' => ['nullable', 'string'],
            'audited_at' => ['nullable', 'date'],
        ]);

        $html = $validated['raw_html'] ?? null;

        if (blank($html)) {
            $response = Http::timeout(15)
                ->withHeaders([
                    'User-Agent' => 'SEO Toolkit Bot/1.0 (+https://seo-toolkit.local)',
                    'Accept' => 'text/html,application/xhtml+xml',
                ])->get($validated['url']);

            if (! $response->successful()) {
                return back()->withErrors([
                    'url' => "Unable to fetch URL. HTTP status {$response->status()}.",
                ])->withInput();
            }

            $html = $response->body();
        }

        $results = $this->analyzeHtml($validated['url'], $html);

        $audit = SeoAudit::create([
            'website_id' => $validated['website_id'] ?? null,
            'url' => $validated['url'],
            'audited_at' => $validated['audited_at'] ?? now(),
            'status' => $results['status'],
            'score' => $results['score'],
            'title' => $results['title'],
            'meta_description' => $results['meta_description'],
            'canonical' => $results['canonical'],
            'h1_count' => $results['h1_count'],
            'image_without_alt' => $results['image_without_alt'],
            'internal_links' => $results['internal_links'],
            'external_links' => $results['external_links'],
            'word_count' => $results['word_count'],
            'issues' => $results['issues'],
        ]);

        return redirect()->route('audits.show', $audit)->with('status', 'Audit completed.');
    }

    /**
     * @return array{
     *     status:string,
     *     score:int,
     *     title:?string,
     *     meta_description:?string,
     *     canonical:?string,
     *     h1_count:int,
     *     image_without_alt:int,
     *     internal_links:int,
     *     external_links:int,
     *     word_count:int,
     *     issues:array<int, string>
     * }
     */
    private function analyzeHtml(string $url, string $html): array
    {
        $sanitizedHtml = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $html) ?? $html;

        libxml_use_internal_errors(true);
        $document = new DOMDocument();
        $document->loadHTML($html);
        libxml_clear_errors();

        $xpath = new DOMXPath($document);

        $titleNode = $xpath->query('//title')->item(0);
        $title = $titleNode ? trim($titleNode->textContent) : null;

        $metaDescription = null;
        $metaNodes = $xpath->query('//meta[@name or @property]');
        if ($metaNodes) {
            foreach ($metaNodes as $metaNode) {
                $name = strtolower(trim((string) $metaNode->attributes?->getNamedItem('name')?->nodeValue));
                if ($name === 'description') {
                    $metaDescription = trim((string) $metaNode->attributes?->getNamedItem('content')?->nodeValue);
                    break;
                }
            }
        }

        $canonical = null;
        $linkNodes = $xpath->query('//link[@rel]');
        if ($linkNodes) {
            foreach ($linkNodes as $linkNode) {
                $rel = strtolower((string) $linkNode->attributes?->getNamedItem('rel')?->nodeValue);
                if (str_contains($rel, 'canonical')) {
                    $canonical = trim((string) $linkNode->attributes?->getNamedItem('href')?->nodeValue);
                    break;
                }
            }
        }

        $h1Count = $xpath->query('//h1')?->length ?? 0;
        $imagesWithoutAlt = $xpath->query('//img[not(@alt) or normalize-space(@alt)=""]')?->length ?? 0;

        $internalLinks = 0;
        $externalLinks = 0;
        $baseHost = parse_url($url, PHP_URL_HOST);

        $anchorNodes = $xpath->query('//a[@href]');
        if ($anchorNodes) {
            foreach ($anchorNodes as $anchorNode) {
                $href = trim((string) $anchorNode->attributes?->getNamedItem('href')?->nodeValue);
                if ($href === '' || str_starts_with($href, '#') || str_starts_with($href, 'mailto:') || str_starts_with($href, 'tel:')) {
                    continue;
                }

                $host = parse_url($href, PHP_URL_HOST);
                if (! $host || $host === $baseHost) {
                    $internalLinks++;
                } else {
                    $externalLinks++;
                }
            }
        }

        $wordCount = str_word_count(strip_tags($sanitizedHtml));

        $issues = [];
        $score = 100;

        $titleLength = $title ? mb_strlen($title) : 0;
        if (! $title) {
            $issues[] = 'Missing <title> tag.';
            $score -= 20;
        } elseif ($titleLength < 35) {
            $issues[] = "Title is short ({$titleLength} chars). Target 35-60.";
            $score -= 8;
        } elseif ($titleLength > 60) {
            $issues[] = "Title is long ({$titleLength} chars). Keep under 60.";
            $score -= 6;
        }

        $metaLength = $metaDescription ? mb_strlen($metaDescription) : 0;
        if (! $metaDescription) {
            $issues[] = 'Missing meta description.';
            $score -= 15;
        } elseif ($metaLength < 70) {
            $issues[] = "Meta description is short ({$metaLength} chars).";
            $score -= 8;
        } elseif ($metaLength > 160) {
            $issues[] = "Meta description is long ({$metaLength} chars).";
            $score -= 6;
        }

        if ($h1Count === 0) {
            $issues[] = 'No H1 found.';
            $score -= 12;
        } elseif ($h1Count > 1) {
            $issues[] = "Multiple H1 tags found ({$h1Count}).";
            $score -= 6;
        }

        if (! $canonical) {
            $issues[] = 'Missing canonical URL.';
            $score -= 7;
        }

        if ($imagesWithoutAlt > 0) {
            $issues[] = "{$imagesWithoutAlt} image(s) without alt text.";
            $score -= min(20, $imagesWithoutAlt * 2);
        }

        if ($wordCount < 300) {
            $issues[] = "Low content depth ({$wordCount} words).";
            $score -= 10;
        }

        if ($internalLinks < 3) {
            $issues[] = "Low internal linking ({$internalLinks} links).";
            $score -= 5;
        }

        $score = max(0, min(100, $score));
        $status = $score >= 80 ? 'pass' : ($score >= 60 ? 'warn' : 'fail');

        return [
            'status' => $status,
            'score' => $score,
            'title' => $title,
            'meta_description' => $metaDescription,
            'canonical' => $canonical,
            'h1_count' => $h1Count,
            'image_without_alt' => $imagesWithoutAlt,
            'internal_links' => $internalLinks,
            'external_links' => $externalLinks,
            'word_count' => $wordCount,
            'issues' => $issues,
        ];
    }
}
