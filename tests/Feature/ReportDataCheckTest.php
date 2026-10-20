<?php

use App\Mail\ReportDataCheckMail;
use App\Models\GscConnection;
use App\Models\Keyword;
use App\Models\RankingSnapshot;
use App\Models\SeoAudit;
use App\Models\UptimeCheck;
use App\Models\User;
use App\Models\Website;
use App\Services\ReportReadinessService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(fn () => Carbon::setTestNow('2026-10-28 08:00:00'));
afterEach(fn () => Carbon::setTestNow());

test('a website without data lists every problem', function () {
    $website = Website::factory()->create();

    $issues = app(ReportReadinessService::class)->issuesForMonth($website, '2026-10');

    expect($issues)->toHaveCount(4)
        ->and($issues[0])->toContain('No uptime checks')
        ->and($issues[1])->toContain('No audit')
        ->and($issues[2])->toContain('No tracked keywords')
        ->and($issues[3])->toContain('not connected');
});

test('a website with complete data is ready', function () {
    $website = Website::factory()->create();
    foreach (range(1, 30) as $day) {
        UptimeCheck::create(['website_id' => $website->id, 'checked_at' => '2026-10-01 00:00:00', 'is_up' => true]);
    }
    SeoAudit::create(['website_id' => $website->id, 'url' => $website->base_url, 'audited_at' => '2026-10-20', 'status' => 'pass', 'score' => 90]);
    $keyword = Keyword::create(['website_id' => $website->id, 'term' => 'shoes']);
    RankingSnapshot::create(['keyword_id' => $keyword->id, 'checked_at' => '2026-10-27', 'position' => 3]);
    GscConnection::factory()->for($website)->create(['last_synced_at' => now()->subDay()]);

    expect(app(ReportReadinessService::class)->issuesForMonth($website, '2026-10'))->toBe([]);
});

test('keywords without a ranking this month are named', function () {
    $website = Website::factory()->create();
    $ranked = Keyword::create(['website_id' => $website->id, 'term' => 'ranked']);
    Keyword::create(['website_id' => $website->id, 'term' => 'missing']);
    RankingSnapshot::create(['keyword_id' => $ranked->id, 'checked_at' => '2026-10-10', 'position' => 5]);

    $issues = app(ReportReadinessService::class)->issuesForMonth($website, '2026-10');

    expect(implode("\n", $issues))->toContain('1 of 2 keyword(s) have no ranking this month: missing');
});

test('the command emails every user when something is missing', function () {
    Mail::fake();
    User::factory()->create(['email' => 'a@example.test']);
    User::factory()->create(['email' => 'b@example.test']);
    Website::factory()->create();

    $this->artisan('seo:check-report-data')->assertExitCode(0);

    Mail::assertSent(ReportDataCheckMail::class, fn (ReportDataCheckMail $mail) => $mail->month === '2026-10'
        && $mail->hasTo('a@example.test') && $mail->hasTo('b@example.test'));

    Mail::fake();
    $this->artisan('seo:check-report-data --no-mail')->assertExitCode(0);
    Mail::assertNothingSent();
});
