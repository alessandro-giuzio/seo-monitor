<?php

namespace App\Console\Commands;

use App\Models\Website;
use App\Services\SeoCrawlerService;
use Illuminate\Console\Command;

class RunScheduledSeoChecks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seo:run-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run scheduled technical SEO crawls for due websites.';

    /**
     * Execute the console command.
     */
    public function handle(SeoCrawlerService $crawlerService): int
    {
        $dueWebsites = Website::query()
            ->where(function ($query) {
                $query->whereNull('next_crawl_at')->orWhere('next_crawl_at', '<=', now());
            })
            ->get();

        if ($dueWebsites->isEmpty()) {
            $this->info('No websites due for crawl.');
            return self::SUCCESS;
        }

        $failures = 0;

        foreach ($dueWebsites as $website) {
            $this->info("Running crawl for {$website->name} ({$website->base_url})");

            try {
                $run = $crawlerService->runForWebsite($website, 30);
                $this->line("Crawled {$run->pages_crawled} pages.");
            } catch (\Throwable $e) {
                $failures++;
                $this->error("Crawl failed for {$website->name}: {$e->getMessage()}");
                report($e);
            }
        }

        if ($failures > 0) {
            $this->warn("{$failures} website(s) failed to crawl — see log for details.");
        }

        return self::SUCCESS;
    }
}
