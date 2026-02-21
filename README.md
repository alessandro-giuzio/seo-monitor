# SEO Toolkit (Laravel)

A local-first SEO operations app built with Laravel.

This project combines research, monitoring, technical checks, and reporting in one dashboard, with modules inspired by day-to-day agency workflows.

## Core Features

- Website management and per-site settings
- Rank tracking (keywords + ranking snapshots)
- On-page SEO audits (live fetch or pasted HTML)
- Domain trend logging (traffic/visibility snapshots)
- Competitor tracking and keyword gap analysis
- Backlink analytics and toxicity flags
- Google Search Console (GSC) manual import
- Technical crawl monitor:
  - robots.txt and sitemap checks
  - indexability checks
  - orphan page detection
  - URL depth tracking
  - hreflang/charset/AMP signals
- Content decay detector (28d vs previous 28d)
- Internal link opportunity generator
- Alerts engine (indexation + traffic drop signals)
- SEO checklist page with pass/warn/fail by section
- Task generation from checklist warn/fail items
- CSV reporting export
- Right-side “How to use this page” guidance panel on all pages

## Tech Stack

- Laravel 12
- PHP 8.2+
- Blade + Tailwind CSS (via Vite)
- SQLite (local default)

## Project Structure (high level)

- `app/Http/Controllers` -> feature controllers
- `app/Services` -> crawler, alert, checklist logic
- `app/Models` -> Eloquent models
- `database/migrations` -> schema
- `resources/views` -> Blade pages
- `routes/web.php` -> web routes
- `routes/console.php` -> scheduled tasks

## Local Setup

1. Install dependencies

```bash
composer install
npm install
```

2. Environment setup

```bash
cp .env.example .env
php artisan key:generate
```

3. Use SQLite (default in this repo)

```bash
touch database/database.sqlite
```

Ensure `.env` contains:

```env
DB_CONNECTION=sqlite
```

4. Run migrations

```bash
php artisan migrate
```

5. Start app

```bash
php artisan serve
npm run dev
```

Open your local URL (for example `http://127.0.0.1:8000` or your Herd domain).

## Main Pages

- `/` Dashboard
- `/websites` Websites
- `/gsc` GSC import
- `/domain-overview` Domain metrics
- `/keyword-research` Keyword ideas
- `/competitors` Competitor gap
- `/backlinks` Backlink analytics
- `/technical` Technical crawl monitor
- `/content-decay` Content decay
- `/link-opportunities` Internal link opportunities
- `/alerts` Alerts
- `/reports` Reports
- `/checklist` SEO checklist + task generation
- `/audits` On-page audits

## Daily Workflow (recommended)

1. Add website on Dashboard
2. Add tracked keywords and ranking snapshots
3. Run technical crawl (`/technical`)
4. Run on-page audits (`/audits`)
5. Import GSC data (`/gsc`)
6. Review decay (`/content-decay`) and alerts (`/alerts`)
7. Open checklist (`/checklist`) and generate tasks
8. Export weekly report (`/reports`)

## GSC Import Formats

The importer supports both:

1. Detailed rows:

```text
date,query,page_url,clicks,impressions,ctr,avg_position
```

2. Search Console Chart export rows:

```text
Date,Clicks,Impressions,CTR,Position
```

Notes:
- `ctr` accepts decimal (`0.0442`) or percent text (`4.42%`)
- `date` should be `YYYY-MM-DD`

## Scheduler and Automation

Scheduled tasks are defined in `routes/console.php`:

- `seo:run-scheduled` (hourly)
- `seo:evaluate-alerts` (hourly)

They run automatically only if Laravel scheduler is running on your machine/server.

For production cron:

```cron
* * * * * php /path/to/project/artisan schedule:run >> /dev/null 2>&1
```

Manual execution:

```bash
php artisan seo:run-scheduled
php artisan seo:evaluate-alerts
```

## Testing

```bash
php artisan test
```

## Deployment Notes

- App works fully local without deployment.
- For production, use MySQL/PostgreSQL instead of SQLite.
- Run migrations with `--force` in production.

## License

MIT
