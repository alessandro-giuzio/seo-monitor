# SEO Toolkit — Next Steps & Project State

> Reference doc for picking up UI work after a break. Written 2026-05-18.

---

## How to run locally

```bash
# Start the app (Laravel Herd handles PHP automatically)
npm run dev          # Vite dev server for CSS/JS hot reload

# Local URL
http://seo-demo.test

# Local login
Email:    giuzio@icloud.com
Password: password

# Production
https://seo.xrun.gdn
```

Git workflow:

```bash
git add .
git commit -m "your message"
git push origin main   # Coolify auto-deploys on push
```

---

## Current state summary

- **27 views** fully implemented across all modules
- **Dark theme** throughout (slate-950 background, sky/emerald accents)
- **Auth-only** — every route requires login, no public registration
- **PostgreSQL** in production (Coolify), **SQLite** locally
- **Queue worker + cron scheduler** running inside the Docker container

---

## Page-by-page UI status

### Auth

| Page | URL | Status | Notes |
|---|---|---|---|
| Login | `/login` | ⚠️ Needs polish | Uses Breeze white guest layout — clashes with dark app theme |
| Forgot password | `/forgot-password` | ⚠️ Needs polish | Same white layout issue |
| Reset password | `/reset-password/{token}` | ⚠️ Needs polish | Same white layout issue |

### Core

| Page | URL | Status | Notes |
|---|---|---|---|
| Dashboard | `/` | ✅ Done | KPI cards, website health grid, recent audits + uptime tables |
| Profile | `/profile` | ✅ Done | Edit name/email/password, delete account |

### Websites

| Page | URL | Status | Notes |
|---|---|---|---|
| Websites list | `/websites` | ✅ Done | Grid of cards + inline "Add website" form (Alpine.js toggle) |
| Website detail | `/websites/{id}` | ✅ Done | Stats, keywords table, uptime checks, update form |

### SEO Audits

| Page | URL | Status | Notes |
|---|---|---|---|
| Audits list | `/audits` | ⚠️ Missing trigger | Table of past audits — but no button to run a new one |
| Audit detail | `/audits/{id}` | ✅ Done | Full report: meta, signals, issues list |

### Keyword Research

| Page | URL | Status | Notes |
|---|---|---|---|
| Keyword ideas | `/keyword-research` | ✅ Done | Single add + bulk CSV import, filters, "track" button |

### Domain Overview

| Page | URL | Status | Notes |
|---|---|---|---|
| Domain trends | `/domain-overview` | ✅ Done | KPIs, snapshot form, trend table |

### GSC

| Page | URL | Status | Notes |
|---|---|---|---|
| GSC import | `/gsc` | ✅ Done | Paste CSV rows, metrics table |

### Backlinks

| Page | URL | Status | Notes |
|---|---|---|---|
| Backlinks | `/backlinks` | ✅ Done | Stats, add form, filters, delete |

### Competitors

| Page | URL | Status | Notes |
|---|---|---|---|
| Competitors | `/competitors` | ✅ Done | Add competitor, add snapshots, keyword gap table |

### Technical SEO

| Page | URL | Status | Notes |
|---|---|---|---|
| Technical | `/technical` | ✅ Done | Run crawl, pages table, filter |
| Crawl run detail | `/technical/runs/{id}` | ✅ Done | Summary + internal link opportunities |

### Alerts

| Page | URL | Status | Notes |
|---|---|---|---|
| Alerts | `/alerts` | ✅ Done | Filter by website/severity, evaluate button, resolve button |

### Content & Analysis

| Page | URL | Status | Notes |
|---|---|---|---|
| Content decay | `/content-decay` | ✅ Done | Pages losing 20%+ traffic vs prior period |
| Link opportunities | `/link-opportunities` | ✅ Done | Internal link suggestions from latest crawl |

### Reports

| Page | URL | Status | Notes |
|---|---|---|---|
| Reports | `/reports` | ✅ Done | 6 KPI cards + CSV export |

### Checklist

| Page | URL | Status | Notes |
|---|---|---|---|
| SEO checklist | `/checklist` | ✅ Done | Generate tasks, mark done, pass/warn/fail badges |

### Operations

| Page | URL | Status | Notes |
|---|---|---|---|
| Change log | `/change-log` | ✅ Done | Log SEO changes, area + impact dropdowns |
| Redirects | `/redirects` | ✅ Done | Add/check/delete redirect rules |
| Release QA | `/release-qa` | ✅ Done | Run QA, scored report, issues list |

---

## Prioritised next steps

Work through these in order — each one is self-contained.

### 1. Restyle the login/auth pages to dark theme ⚠️ HIGH

**Problem:** Login, forgot-password and reset-password use the Breeze white `<x-guest-layout>`. After login the whole app is dark — the white login page feels like a different product.

**Fix:** Replace `<x-guest-layout>` with a custom dark login layout. Match slate-950 background, centered card, sky-500 submit button.

**Files to change:**

- `resources/views/auth/login.blade.php`
- `resources/views/auth/forgot-password.blade.php`
- `resources/views/auth/reset-password.blade.php`
- `resources/views/layouts/guest.blade.php` (or create `resources/views/components/layouts/guest.blade.php`)

---

### 2. Add "Run audit" button to the audits page ⚠️ HIGH

**Problem:** `POST /audits` works and the controller is fully implemented, but there's no button in the UI to trigger it. Users have to know the URL and POST manually.

**Fix:** Add a small form at the top of `/audits` with a website selector and URL field, posting to `route('audits.store')`.

**Files to change:**

- `resources/views/audits/index.blade.php`

**What `store()` needs:**

- `website_id` (integer)
- `url` (the page URL to audit)
- `raw_html` (optional — if blank, controller fetches the URL itself)

---

### 3. Active nav link highlighting ⚠️ MEDIUM

**Problem:** The navigation bar in `resources/views/components/layouts/app.blade.php` has no active state — all links look the same regardless of which page you're on.

**Fix:** Use `request()->routeIs('dashboard')` etc. to add `border-sky-400 text-sky-300` classes to the current page's link.

**File to change:**

- `resources/views/components/layouts/app.blade.php` (lines 18–32, the nav links)

---

### 4. Flash message toasts ⚠️ MEDIUM

**Problem:** After form submissions (add website, delete keyword, etc.) Laravel sets a session flash `status` message, but nothing in the layout renders it visually. Users get no confirmation that their action worked.

**Fix:** Add a toast/banner component to the main layout that reads `session('status')` and `session('error')` and shows a dismissible message.

**File to change:**

- `resources/views/components/layouts/app.blade.php` (add banner after `<header>`)

---

### 5. Empty states on list pages ⚠️ MEDIUM

**Problem:** Several pages (backlinks, competitors, alerts, keyword research) show a blank table with no rows and no explanation when there's no data yet.

**Fix:** Add an empty state block inside each `@forelse` / `@empty` section: a short message + a link or button to add the first item.

**Files to change:**

- `resources/views/backlinks/index.blade.php`
- `resources/views/competitors/index.blade.php`
- `resources/views/alerts/index.blade.php`
- `resources/views/keyword-research/index.blade.php`

---

### 6. Website edit/delete from the index page ⚠️ LOW

**Problem:** To edit or delete a website you have to click into `/websites/{id}` first. It would be faster to have a small "⋯" menu on each card in the grid.

**Fix:** Add a dropdown (Alpine.js, already used elsewhere) to each website card in `websites/index.blade.php` with Edit and Delete options.

**File to change:**

- `resources/views/websites/index.blade.php`

---

### 7. Keyword inline edit ⚠️ LOW

**Problem:** Once a keyword is added on the website detail page, there's no way to edit it — only delete.

**Fix:** Add an inline edit toggle on the keyword row in `websites/show.blade.php`.

**File to change:**

- `resources/views/websites/show.blade.php`

---

## Key files map

```
app/
  Http/Controllers/          ← all backend logic lives here
    DashboardController.php
    WebsiteController.php
    SeoAuditController.php
    KeywordController.php
    KeywordResearchController.php
    BacklinkController.php
    CompetitorController.php
    TechnicalSeoController.php
    AlertController.php
    ContentDecayController.php
    LinkOpportunityController.php
    ReportController.php
    SeoChecklistController.php
    SeoChangeLogController.php
    RedirectManagerController.php
    ReleaseQaController.php
    GscController.php

resources/views/
  components/layouts/app.blade.php   ← dark layout used by all authenticated pages
  layouts/guest.blade.php            ← white layout used by login/auth pages (needs restyle)
  dashboard.blade.php
  websites/
    index.blade.php
    show.blade.php
  audits/
    index.blade.php
    show.blade.php
  auth/
    login.blade.php
    forgot-password.blade.php
    reset-password.blade.php

routes/
  web.php     ← all authenticated routes
  auth.php    ← login/logout/password reset routes

docker/
  entrypoint.sh      ← runs migrations + caches on deploy
  supervisord.conf   ← manages nginx, php-fpm, queue worker, cron
```

---

## Deployment reminder

Coolify auto-deploys when you push to `main`. After a deploy:

1. Check the Coolify **Logs** tab — look for `Starting supervisord` at the end
2. Visit `https://seo.xrun.gdn/up` — should return `{"status":"ok"}`
3. Visit the app and confirm the dashboard loads

If you need to run a command in production:

```
Coolify → your app → Terminal tab → run php artisan commands here
```
