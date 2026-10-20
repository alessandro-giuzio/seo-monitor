<?php

namespace App\Console\Commands;

use App\Models\Website;
use App\Services\SeoAuditService;
use Illuminate\Console\Command;

class RunScheduledAudits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seo:audit-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Run an on-page audit of every website's base URL.";

    /**
     * Execute the console command.
     */
    public function handle(SeoAuditService $audits): int
    {
        $failures = 0;

        foreach (Website::query()->get() as $website) {
            try {
                $audit = $audits->audit($website, $website->base_url);
                $this->line("{$website->name}: score {$audit->score}");
            } catch (\Throwable $e) {
                $failures++;
                $this->error("Audit failed for {$website->name}: {$e->getMessage()}");
                report($e);
            }
        }

        if ($failures > 0) {
            $this->warn("{$failures} website(s) failed to audit — see log for details.");
        }

        return self::SUCCESS;
    }
}
