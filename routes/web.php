<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DomainOverviewController;
use App\Http\Controllers\KeywordController;
use App\Http\Controllers\KeywordResearchController;
use App\Http\Controllers\LinkOpportunityController;
use App\Http\Controllers\ContentDecayController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RankingSnapshotController;
use App\Http\Controllers\SeoAuditController;
use App\Http\Controllers\SeoChangeLogController;
use App\Http\Controllers\SeoChecklistController;
use App\Http\Controllers\GscController;
use App\Http\Controllers\TechnicalSeoController;
use App\Http\Controllers\AlertController;
use App\Http\Controllers\RedirectManagerController;
use App\Http\Controllers\ReleaseQaController;
use App\Http\Controllers\UptimeCheckController;
use App\Http\Controllers\WebsiteController;
use App\Http\Controllers\BacklinkController;
use App\Http\Controllers\CompetitorController;
use Illuminate\Support\Facades\Route;

Route::get('/', DashboardController::class)->name('dashboard');

Route::get('/websites', [WebsiteController::class, 'index'])->name('websites.index');
Route::get('/websites/{website}', [WebsiteController::class, 'show'])->name('websites.show');
Route::post('/websites', [WebsiteController::class, 'store'])->name('websites.store');
Route::put('/websites/{website}', [WebsiteController::class, 'update'])->name('websites.update');
Route::delete('/websites/{website}', [WebsiteController::class, 'destroy'])->name('websites.destroy');

Route::post('/websites/{website}/keywords', [KeywordController::class, 'store'])->name('keywords.store');
Route::delete('/keywords/{keyword}', [KeywordController::class, 'destroy'])->name('keywords.destroy');

Route::post('/keywords/{keyword}/ranking-snapshots', [RankingSnapshotController::class, 'store'])->name('rankings.store');
Route::post('/websites/{website}/uptime-checks', [UptimeCheckController::class, 'store'])->name('uptime.store');

Route::get('/audits', [SeoAuditController::class, 'index'])->name('audits.index');
Route::get('/audits/{audit}', [SeoAuditController::class, 'show'])->name('audits.show');
Route::post('/audits', [SeoAuditController::class, 'store'])->name('audits.store');

Route::get('/domain-overview', [DomainOverviewController::class, 'index'])->name('domain-overview.index');
Route::post('/websites/{website}/domain-overview', [DomainOverviewController::class, 'store'])->name('domain-overview.store');

Route::get('/keyword-research', [KeywordResearchController::class, 'index'])->name('keyword-research.index');
Route::post('/keyword-research', [KeywordResearchController::class, 'store'])->name('keyword-research.store');
Route::post('/keyword-research/bulk', [KeywordResearchController::class, 'bulkStore'])->name('keyword-research.bulk-store');
Route::post('/keyword-research/{idea}/track', [KeywordResearchController::class, 'track'])->name('keyword-research.track');

Route::get('/backlinks', [BacklinkController::class, 'index'])->name('backlinks.index');
Route::post('/backlinks', [BacklinkController::class, 'store'])->name('backlinks.store');
Route::delete('/backlinks/{backlink}', [BacklinkController::class, 'destroy'])->name('backlinks.destroy');

Route::get('/competitors', [CompetitorController::class, 'index'])->name('competitors.index');
Route::post('/websites/{website}/competitors', [CompetitorController::class, 'store'])->name('competitors.store');
Route::post('/competitors/{competitor}/snapshots', [CompetitorController::class, 'storeSnapshot'])->name('competitors.snapshots.store');
Route::delete('/competitors/{competitor}', [CompetitorController::class, 'destroy'])->name('competitors.destroy');

Route::get('/gsc', [GscController::class, 'index'])->name('gsc.index');
Route::post('/gsc/import', [GscController::class, 'import'])->name('gsc.import');

Route::get('/technical', [TechnicalSeoController::class, 'index'])->name('technical.index');
Route::post('/technical/run', [TechnicalSeoController::class, 'run'])->name('technical.run');
Route::get('/technical/runs/{run}', [TechnicalSeoController::class, 'show'])->name('technical.runs.show');

Route::get('/alerts', [AlertController::class, 'index'])->name('alerts.index');
Route::post('/alerts/evaluate', [AlertController::class, 'evaluate'])->name('alerts.evaluate');
Route::post('/alerts/{alert}/resolve', [AlertController::class, 'resolve'])->name('alerts.resolve');

Route::get('/content-decay', [ContentDecayController::class, 'index'])->name('decay.index');
Route::get('/link-opportunities', [LinkOpportunityController::class, 'index'])->name('links.index');

Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
Route::get('/reports/export/csv', [ReportController::class, 'exportCsv'])->name('reports.csv');
Route::get('/checklist', [SeoChecklistController::class, 'index'])->name('checklist.index');
Route::post('/checklist/generate-tasks', [SeoChecklistController::class, 'generateTasks'])->name('checklist.tasks.generate');
Route::post('/checklist/tasks/{task}/complete', [SeoChecklistController::class, 'completeTask'])->name('checklist.tasks.complete');

Route::get('/change-log', [SeoChangeLogController::class, 'index'])->name('change-log.index');
Route::post('/change-log', [SeoChangeLogController::class, 'store'])->name('change-log.store');
Route::delete('/change-log/{log}', [SeoChangeLogController::class, 'destroy'])->name('change-log.destroy');

Route::get('/redirects', [RedirectManagerController::class, 'index'])->name('redirects.index');
Route::post('/redirects', [RedirectManagerController::class, 'store'])->name('redirects.store');
Route::post('/redirects/{rule}/check', [RedirectManagerController::class, 'check'])->name('redirects.check');
Route::delete('/redirects/{rule}', [RedirectManagerController::class, 'destroy'])->name('redirects.destroy');

Route::get('/release-qa', [ReleaseQaController::class, 'index'])->name('release-qa.index');
Route::post('/release-qa/run', [ReleaseQaController::class, 'run'])->name('release-qa.run');
Route::get('/release-qa/{run}', [ReleaseQaController::class, 'show'])->name('release-qa.show');
