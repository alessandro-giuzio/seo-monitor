<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('seo:run-scheduled')->hourly();
Schedule::command('seo:evaluate-alerts')->hourly();
Schedule::command('gsc:sync-scheduled')->dailyAt('03:00');
Schedule::command('seo:sync-gsc-rankings')->dailyAt('03:30');
Schedule::command('seo:check-uptime')->everyFiveMinutes()->withoutOverlapping();
// Weekly, plus late on the last day of the month (UTC, still the same day in Madrid) so the
// portal's monthly report always has a fresh audit score.
Schedule::command('seo:audit-scheduled')->weeklyOn(1, '04:00');
Schedule::command('seo:audit-scheduled')->lastDayOfMonth('20:00');
// A few days before the portal drafts monthly reports on the 1st.
Schedule::command('seo:check-report-data')->monthlyOn(28, '08:00');
