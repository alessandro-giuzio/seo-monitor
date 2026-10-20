<?php

namespace App\Console\Commands;

use App\Models\Website;
use App\Services\UptimeMonitorService;
use Illuminate\Console\Command;

class CheckUptime extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seo:check-uptime';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Record an automatic uptime check for every website.';

    /**
     * Execute the console command.
     */
    public function handle(UptimeMonitorService $monitor): int
    {
        foreach (Website::query()->get() as $website) {
            try {
                $check = $monitor->check($website);
                $this->line("{$website->name}: ".($check->is_up ? 'up' : 'down')." ({$check->status_code})");
            } catch (\Throwable $e) {
                $this->error("Uptime check failed for {$website->name}: {$e->getMessage()}");
                report($e);
            }
        }

        return self::SUCCESS;
    }
}
