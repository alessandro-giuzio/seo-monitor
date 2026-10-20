<?php

namespace App\Console\Commands;

use App\Mail\ReportDataCheckMail;
use App\Models\User;
use App\Models\Website;
use App\Services\PortalReportService;
use App\Services\ReportReadinessService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class CheckReportData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seo:check-report-data
                            {--month= : Month to check (YYYY-MM, default: the current Madrid month)}
                            {--no-mail : Only print the result}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check every website has the data its monthly portal report needs, and email the users if not.';

    /**
     * Execute the console command.
     */
    public function handle(ReportReadinessService $readiness): int
    {
        $month = $this->option('month') ?: now(PortalReportService::TIMEZONE)->format('Y-m');
        if (! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) {
            $this->error('--month must be YYYY-MM.');

            return self::INVALID;
        }

        $problems = [];
        foreach (Website::query()->orderBy('name')->get() as $website) {
            $issues = $readiness->issuesForMonth($website, $month);
            if ($issues === []) {
                $this->info("{$website->name}: ready");

                continue;
            }
            $problems[] = ['website' => $website, 'issues' => $issues];
            $this->warn("{$website->name}:");
            foreach ($issues as $issue) {
                $this->line("  - {$issue}");
            }
        }

        if ($problems !== [] && ! $this->option('no-mail')) {
            $recipients = User::query()->pluck('email')->all();
            if ($recipients !== []) {
                Mail::to($recipients)->send(new ReportDataCheckMail($month, $problems));
                $this->info('Emailed '.count($recipients).' user(s).');
            }
        }

        return self::SUCCESS;
    }
}
