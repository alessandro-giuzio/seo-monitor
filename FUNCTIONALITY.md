# SEO Toolkit — Functionality Guide

For step-by-step routines (onboarding a client, weekly checks, month end), see [HOW_TO_USE.md](HOW_TO_USE.md). This file is the reference.

What this app does, how its modules depend on each other, what it shares with the client portal, and what you have to feed it by hand. Written for the operator running SEO monitoring for one or more client websites.

Last reviewed: 2026-10-20 (portal API, plus the automation pass: uptime monitor, scheduled audits, GSC rankings, alert emails, report data check).

---

## The big picture

This is a **self-hosted SEO operations hub** (https://seo.xrun.gdn, login only). Data gets in through three channels, and data goes out through one:

| Direction | Channel | Modules |
| --- | --- | --- |
| In | **Live fetching** — the app makes real HTTP requests to the site | Technical crawls, on-page Audits, Redirect checks, uptime checks (every 5 min) |
| In | **Google Search Console API** — OAuth once per website, then daily auto-sync | GSC (feeds Content Decay, traffic-drop alerts, Reports, Checklist, and keyword rankings) |
| In | **Manual entry** — you type or paste numbers from elsewhere | Domain Overview, Competitors, Backlinks, Keyword Research, Change Log; rankings/uptime only as a supplement |
| Out | **Client portal API** — read-only JSON for apps.xrun.gdn | Website status card + monthly client reports |
| Out | **Email** — needs `MAIL_*` configured | New-alert emails, monthly report data check |

Almost every page is scoped to **one website at a time** via a picker at the top. Add your website(s) first — nothing else works until at least one exists. There is also an in-app **Guide** page (`/guide`) with a short version of the setup steps below.

---

## Recommended order of operations

1. **Websites** — add each site (name + base URL required). Fill in the GSC property (`sc-domain:example.com`) now; you need it for step 3. Set an **alert email** if you want new alerts emailed. Industry, target country and crawl frequency are optional but unlock other features. Uptime checks start automatically within 5 minutes.
2. **Website detail page** — add the keywords you want to track. Use the exact search term as people type it: rankings are matched against Search Console queries (case-insensitive, exact match).
3. **GSC** — click **Connect Google Search Console** and authorize. From then on it syncs daily at 03:00 by itself. (Manual CSV paste is still there as a fallback.) This feeds Content Decay, the traffic-drop alert, Reports and one Checklist item.
4. **Technical** — run a crawl. Feeds Link Opportunities, two alert types (orphan / non-indexable pages), most of the Checklist, and Release QA. After the first run the hourly scheduler re-crawls according to the site's crawl frequency.
5. **Audits** — the homepage (base URL) is audited automatically every Monday and on the last day of the month. Audit other key URLs by hand, **with the website selected** (an audit without a website is not counted anywhere: Checklist, Release QA and the portal all ignore it).
6. **Alerts → Run evaluation** — now that crawl + GSC data exist it finds real issues. It also runs hourly on its own.
7. **If the client uses the portal** — link the website to the client in the portal (see "Client portal connection" below). Uptime, audits and (with GSC connected) rankings now fill themselves; the check on the 28th emails you anything still missing.

Reports, Release QA and Checklist then become meaningful, since they aggregate steps 3–6. Everything else (Keyword Research, Domain Overview, Competitors, Backlinks, Redirects, Change Log) is independent and can be used any time.

---

## Module-by-module reference

### Dashboard (`/`)
Read-only landing page. KPI tiles (sites, tracked keywords, keywords in the top 10, open alerts, 30-day uptime %, audit count) link to their pages. Below: a health card per website (uptime dot, latest audit score, keyword count) and recent audits / uptime checks.

### Guide (`/guide`)
In-app quick-start: the setup steps above in short form. Nothing to configure.

### Websites (`/websites`)
The root of everything. Name + base URL; optional GSC property, industry, 2-letter target country, alert email, crawl frequency in hours (used by the hourly scheduler to decide when a site is due for a crawl). Each card has a "⋯" menu to edit or delete. **The website's numeric id (visible in the URL `/websites/{id}`) is what you enter in the portal** to link a client to it.

### Website detail (`/websites/{id}`)
The control panel for one site:
- **Update Website** — same settings as the add form.
- **Latest Monitoring** — most recent uptime check, audit, crawl and open-alert count.
- **Keywords** — add keywords (term, optional target URL, search engine, location, device, priority 1–3). Per row: **Log ranking** (position you observed, search volume, difficulty, SERP features), **Edit**, **Delete**. When Search Console is connected, a ranking is also logged **automatically every day** (see GSC below); those show a green **GSC** badge next to the position.
- **Recent Uptime Checks** — the last 20 checks. An **automatic check runs every 5 minutes**: one GET of the base URL (10 s timeout, redirects followed); up = final status below 400. Unreachable sites are recorded as down with the error in the notes. **Record check** still lets you add one by hand.

### GSC (`/gsc`)
The most important data source — it powers Content Decay, the traffic-drop alert and Reports.
- **Connect Google Search Console** — one-time OAuth per website (needs the website's GSC property set). Syncs daily at 03:00; **Sync now** pulls on demand; **Disconnect** removes the stored tokens. Needs `GOOGLE_CLIENT_ID` / `GOOGLE_CLIENT_SECRET` configured (see `CLAUDE.md`). Without them the button shows a friendly error.
- **Rankings from GSC** — every day at 03:30, for each tracked keyword whose term matches a GSC query, the app logs a ranking: the impression-weighted average position over the 7 days ending at the latest imported date (GSC data lags 2–3 days). One snapshot per keyword per day (`source = gsc`). This is Google's *average* position, not a single live SERP check. It also works on pasted 7-column data.
- **Manual paste fallback** — for unconnected sites or historical backfills. Auto-detected by column count: 5 columns (`date,clicks,impressions,ctr,position`) or 7 columns (`date,query,page_url,clicks,impressions,ctr,avg_position`). CTR as decimal or `%`.

### Technical (`/technical`)
The real crawler. **Run crawl** fetches `robots.txt` and `sitemap.xml`, then crawls up to N pages from the sitemap (default 30, max 200; falls back to the homepage if there's no sitemap). Per page: title, canonical, meta robots, H1 count, word count, internal links, URL depth, hreflang count, charset, AMP, indexable (200, not noindex, self-canonical) and orphaned (no internal links pointing to it, not the homepage). Afterwards it generates **internal link opportunities**. An unreachable site marks the run `failed` without blocking other sites. Runs automatically per the site's crawl frequency.

### Link Opportunities (`/link-opportunities`)
Read-only output of the crawler's internal-linking suggestions. Re-run a crawl to refresh.

### Audits (`/audits`)
On-page analysis of one URL. Fetches the URL live, or analyzes pasted HTML (pages behind auth, pre-production content). Checks title length (35–60), meta description (70–160), exactly one H1, canonical, images without `alt`, word count (300+), internal/external links. Gives a 0–100 score and pass/warn/fail with an issue list. Unreachable URLs give a validation error, not a crash. Each website's **base URL is audited automatically** every Monday 04:00 and on the last day of the month at 20:00 (UTC). Other URLs are audited by hand: pick the website in the form, otherwise the audit is unattached.

### Content Decay (`/content-decay`)
Pages whose GSC clicks dropped ≥20% (last 28 days vs. the 28 before), ignoring pages with fewer than 20 clicks in the earlier period. Empty without GSC data.

### Alerts (`/alerts`)
Filterable feed (website, severity, open/resolved). **Run evaluation** checks every website for orphan pages and non-indexable pages (latest crawl) and page traffic drops ≥30% (GSC, `high` severity). No duplicate alert for the same issue within 24 hours. Runs hourly on its own. **Newly created alerts are emailed** to the website's alert email (one email per evaluation, listing all new alerts; also when you click the button). A mail failure is logged and never stops the evaluation. **Resolve** closes an alert. Open alert count is shown to clients in the portal status card.

### Reports (`/reports`)
Internal one-page rollup per website: tracked keywords, top-10 count, open alerts, 30-day uptime, latest crawl's indexable/orphan counts, top 20 decaying pages. **Export CSV** for sharing. (The client-facing monthly report lives in the portal — see below.)

### Checklist (`/checklist`)
Auto-graded 4-section checklist (crawlability, on-page, technical, international) built from the latest crawl, latest audit, GSC data and website settings; each item pass/warn/fail with a reason. **Generate tasks** turns warn/fail items into tasks (due in 3 days for fails, 7 for warnings, no duplicates). Mark tasks **done** as you fix them.

### Release QA (`/release-qa`)
Pre-deploy gate. Pick environment (staging/production/preview) and an optional release tag; it scores crawl health, the latest audit score, open alerts and failing redirect checks into 0–100 with a pass/warn/fail verdict and an issue list.

### Redirects (`/redirects`)
Redirect rules per website (from-path, destination, 301/302/307/308, active, notes). **Check** live-fetches the from-path without following it and verifies the status and `Location` header. Failing checks feed Release QA.

### Domain Overview (`/domain-overview`)
Dated snapshots of third-party domain metrics (estimated traffic, organic keywords, referring domains, backlinks, 0–100 visibility, average position), plotted as a trend. Manual entry.

### Keyword Research (`/keyword-research`)
Keyword idea database, separate from tracked keywords. Add one at a time or bulk-paste CSV (`keyword,volume,kd,cpc,intent`). Filters: website, text, country, intent, min volume, max difficulty. **Track** promotes an idea into a tracked keyword on its website.

### Competitors / Keyword Gap (`/competitors`)
Add competitors per website and log their ranking snapshots (keyword, date, position, volume) by hand. The **Keyword Gap** table lists keywords where a competitor is in the top 30 and you don't rank or rank worse, sorted by volume and competitor position.

### Backlinks (`/backlinks`)
Log backlinks found elsewhere (source, target, anchor, authority, nofollow/toxic flags, found/last-seen dates). Filters for toxic/nofollow; stats tiles. No live discovery.

### Change Log (`/change-log`)
Manual audit trail: area (content, metadata, technical, internal links, redirects, schema, other), date, old/new value, impact. Nothing reads it automatically; useful for correlating later ranking/traffic shifts.

### Profile
Name, email, password, account deletion.

---

## Client portal connection (apps.xrun.gdn)

The client portal (separate Astro app, repo `xrun`) shows each client data about their website. It pulls that data from this app **server-side** through a small read-only JSON API. Clients never call seo-monitor directly and never see the token.

### Setup

| Where | Setting |
| --- | --- |
| seo-monitor (Coolify env) | `PORTAL_API_TOKEN=<long random string>`. If it's empty or unset, every API request gets 401. |
| Portal (Coolify env) | `SEO_MONITOR_URL=https://seo.xrun.gdn` and the **same** `PORTAL_API_TOKEN` |
| Portal admin → client | Set the client's **seo-monitor website id** (the `{id}` from `/websites/{id}` here) |

If either env var is missing on the portal side, or the client has no website id, the status card is simply hidden.

### Endpoints

Both need `Authorization: Bearer <PORTAL_API_TOKEN>`. The token is checked before the website lookup: bad token → 401, unknown website → 404.

**`GET /api/portal/websites/{id}/status`**: the live status card on the client dashboard. The portal caches it for 10 minutes (failures: 1 minute).
- `website` — id, name, base URL
- `uptime` — latest check up/down + time, 30-day uptime %, 30-day average response (`null` if no checks were ever logged)
- `audit` — latest audit score + date for this website
- `open_alerts` — count of unresolved alerts
- `keywords` — the 5 best-ranking keywords with current and previous position

**`GET /api/portal/websites/{id}/report?month=YYYY-MM`**: figures for the monthly client report. Months are calendar months in Madrid time. Not cached.
- `uptime` — uptime %, average response, number of incidents (an incident is a run of consecutive "down" checks) for that month; `null` if no checks were logged that month
- `audit` — the last audit score before month end, plus the last score before month start (for "was X, now Y")
- `keywords` — up to 10 keywords, best end position first, each with position at the start and end of the month (= the last ranking logged before each date). Keywords with no ranking logged by month end are left out.

### Monthly cycle

On the **1st of each month** the portal's `monthly` job drafts a report for the previous month for every client with activity or a seo-monitor link, calls the report endpoint, freezes the numbers into the draft, and emails the admin. You review/edit the draft in the portal and publish it; only published reports are visible to the client.

**Important consequence:** a report is only as good as the data here for that month. With the automation in place:
1. **Uptime** — automatic every 5 minutes.
2. **Audit** — the homepage is audited automatically on the last day of the month, so the report's score is always current.
3. **Rankings** — automatic daily for keywords that appear in Search Console data. Keywords with no GSC impressions (or sites without GSC) still need **Log ranking** by hand.

On the **28th at 08:00** `seo:check-report-data` checks every website for the current month and emails **all users** a list of what's missing: no/too few uptime checks, no audit attached this month, keywords with no ranking this month, Search Console not connected or not synced for 3+ days. Run it any time with `php artisan seo:check-report-data --no-mail` (or `--month=2026-09`).

If you fix data after the draft was made, **regenerate** the draft in the portal (drafts can be regenerated; published reports must be unpublished first).

Note: the "audit score" the client sees is simply the most recent audit for that website, whatever URL it was. The month-end automatic homepage audit runs last, so normally the client sees the homepage score. If you audit another URL by hand after that run, that score is what the client sees instead.

---

## What runs automatically vs. what you trigger

**Automatic (Laravel scheduler in the container):**

| Command | When | Does |
| --- | --- | --- |
| `seo:run-scheduled` | hourly | Crawls every website whose `next_crawl_at` is due |
| `seo:evaluate-alerts` | hourly | Same as "Run evaluation" on `/alerts`, for all websites |
| `gsc:sync-scheduled` | daily 03:00 | Pulls GSC data for every connected website |
| `seo:sync-gsc-rankings` | daily 03:30 | Logs a GSC-based ranking for each tracked keyword |
| `seo:check-uptime` | every 5 min | Uptime check of every website's base URL |
| `seo:audit-scheduled` | Mon 04:00 + last day of month 20:00 | On-page audit of every website's base URL |
| `seo:check-report-data` | 28th, 08:00 | Emails users what's missing for the monthly report |
| Portal `monthly` job (in the portal) | 1st of month | Calls `/report` and drafts client reports |

All times are UTC (the app timezone). Every scheduled command keeps going when one website fails.

**Manual only:** rankings for keywords not in GSC, audits of URLs other than the homepage, redirect checks, Release QA runs, checklist task generation, Domain Overview / Competitors / Backlinks / Keyword Research / Change Log entries, and GSC paste for unconnected sites.

---

## Known gaps

- **Emails need mail configured in production.** `MAIL_MAILER` defaults to `log`; set the `MAIL_*` variables in Coolify (the portal's SMTP account works) or alert and report-check emails only end up in the logs.
- **GSC rankings are averages and need exact-match terms.** A keyword is matched only if its term equals a GSC query (case-insensitive). Low-volume keywords may never appear in GSC and still need manual logging.
- **Uptime is checked from the same server that hosts this app.** If the Hetzner VPS itself is down, nothing is recorded (a gap, not "down").
- **Uptime rows grow (~290 per website per day).** Fine for years at this scale, but there's no pruning.
- **Tests:** feature tests exist for the portal API, every scheduled command (crawl, GSC sync, GSC rankings, uptime, audits, report check), alert emails, the GROUP BY regression and `/competitors`. Most controllers still have no tests.
- **Domain Overview, Competitors, Backlinks** trends depend on you logging snapshots consistently; there's no reminder.
- Production is PostgreSQL, local is SQLite. Aggregate queries on `Website` relations need `->reorder()` (see `CLAUDE.md` / `PLAN.md`).
