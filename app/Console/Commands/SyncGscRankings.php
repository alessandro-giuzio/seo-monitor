<?php

namespace App\Console\Commands;

use App\Models\Website;
use App\Services\GscRankingService;
use Illuminate\Console\Command;

class SyncGscRankings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seo:sync-gsc-rankings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Log keyword rankings from imported Search Console data.';

    /**
     * Execute the console command.
     */
    public function handle(GscRankingService $rankings): int
    {
        foreach (Website::query()->whereHas('keywords')->get() as $website) {
            try {
                $count = $rankings->syncForWebsite($website);
                $this->line("{$website->name}: {$count} keyword(s) updated");
            } catch (\Throwable $e) {
                $this->error("Ranking sync failed for {$website->name}: {$e->getMessage()}");
                report($e);
            }
        }

        return self::SUCCESS;
    }
}
