<?php

use App\Mail\NewSeoAlertsMail;
use App\Models\GscMetric;
use App\Models\Website;
use App\Services\SeoAlertService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

function websiteWithTrafficDrop(array $attributes = []): Website
{
    $website = Website::factory()->create($attributes);
    GscMetric::factory()->for($website)->create([
        'metric_date' => now()->subDays(40)->toDateString(), 'page_url' => 'https://a.test/p', 'clicks' => 100,
    ]);
    GscMetric::factory()->for($website)->create([
        'metric_date' => now()->subDays(5)->toDateString(), 'page_url' => 'https://a.test/p', 'clicks' => 10,
    ]);

    return $website;
}

test('new alerts are emailed to the website alert email, once', function () {
    Mail::fake();
    $website = websiteWithTrafficDrop(['alert_email' => 'ops@example.test']);

    $this->artisan('seo:evaluate-alerts')->assertExitCode(0);
    $this->artisan('seo:evaluate-alerts')->assertExitCode(0); // duplicate alert, no second mail

    Mail::assertSent(NewSeoAlertsMail::class, 1);
    Mail::assertSent(NewSeoAlertsMail::class, fn (NewSeoAlertsMail $mail) => $mail->hasTo('ops@example.test')
        && $mail->website->is($website)
        && count($mail->alerts) === 1);
});

test('no email without an alert email, and a mail failure does not break evaluation', function () {
    Mail::fake();
    websiteWithTrafficDrop();
    $this->artisan('seo:evaluate-alerts')->assertExitCode(0);
    Mail::assertNothingSent();

    Mail::shouldReceive('to')->andThrow(new RuntimeException('SMTP down'));
    $website = websiteWithTrafficDrop(['alert_email' => 'ops@example.test']);
    expect(app(SeoAlertService::class)->evaluateForWebsite($website))->toBe(1);
});
