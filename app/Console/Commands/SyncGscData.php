<?php

namespace App\Console\Commands;

use App\Models\Website;
use App\Services\GscSyncService;
use Illuminate\Console\Command;

class SyncGscData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gsc:sync-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Google Search Console data for connected websites.';

    /**
     * Execute the console command.
     */
    public function handle(GscSyncService $syncService): int
    {
        $connectedWebsites = Website::query()->whereHas('gscConnection')->get();

        if ($connectedWebsites->isEmpty()) {
            $this->info('No websites connected to Google Search Console.');
            return self::SUCCESS;
        }

        $failures = 0;

        foreach ($connectedWebsites as $website) {
            $this->info("Syncing GSC data for {$website->name}");

            try {
                $created = $syncService->syncForWebsite($website);
                $this->line("Synced {$created} row(s).");
            } catch (\Throwable $e) {
                $failures++;
                $this->error("Sync failed for {$website->name}: {$e->getMessage()}");
                report($e);
            }
        }

        if ($failures > 0) {
            $this->warn("{$failures} website(s) failed to sync — see log for details.");
        }

        return self::SUCCESS;
    }
}
