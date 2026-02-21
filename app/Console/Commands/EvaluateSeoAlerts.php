<?php

namespace App\Console\Commands;

use App\Models\Website;
use App\Services\SeoAlertService;
use Illuminate\Console\Command;

class EvaluateSeoAlerts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seo:evaluate-alerts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Evaluate alert rules for all websites.';

    /**
     * Execute the console command.
     */
    public function handle(SeoAlertService $alertService): int
    {
        $created = 0;
        $websites = Website::query()->get();

        foreach ($websites as $website) {
            $newCount = $alertService->evaluateForWebsite($website);
            $created += $newCount;
            $this->line("{$website->name}: {$newCount} new alert(s)");
        }

        $this->info("Total created alerts: {$created}");

        return self::SUCCESS;
    }
}
