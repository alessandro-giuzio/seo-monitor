# SEO Toolkit — Functionality Guide

A complete reference for what this app does, how its 17 modules connect to each other, and what to feed it to get useful output. Written for a real operator sitting down to run SEO monitoring for one or more websites.

---

## The big picture

This is a **self-hosted SEO operations hub**, not an all-in-one SEO API product. It does two very different things depending on the page:

1. **Live fetching** — a handful of features make real HTTP requests to your website right now: **on-page Audits**, **Technical crawls**, and **Redirect checks**. Point these at a real URL and they'll fetch it live.
2. **Manual data entry** — everything else (Search Console numbers, domain authority metrics, competitor rankings, backlinks, uptime checks, keyword rank tracking) has **no live third-party API connection**. You paste in numbers you already have (from Google Search Console exports, Ahrefs/Semrush/Moz, or your own monitoring), and the toolkit stores, trends, and cross-references them.

Almost every page is scoped to **one website at a time** via a website picker at the top. Add your website(s) first — nothing else is useful until at least one exists.

---

## Recommended order of operations

The pages aren't independent — several depend on data that only exists once you've used another page first. Set up in roughly this order:

1. **Websites** — add each site you're tracking (name + URL required; GSC property, industry, target country, alert email, crawl frequency are optional but unlock other features).
2. **Websites → detail page** — add the keywords you want to track for that site.
3. **GSC** — paste in a Search Console export. This single step powers three other pages: Content Decay, the traffic-drop alert type, and one of the Checklist's Core Web Vitals checks.
4. **Technical** — run a crawl. This powers: Link Opportunities (auto-generated), two Alert types (orphan pages, non-indexable pages), most of the Checklist's crawlability/on-page section, and Release QA's crawl-health checks.
5. **Audits** — run an on-page audit for your key URLs (title, meta description, H1, alt text, word count). Feeds the Checklist's on-page section and Release QA's on-page score.
6. **Alerts → Run evaluation** — now that crawl + GSC data exist, this actually finds something. Run it periodically (it also runs automatically every hour via the scheduler).
7. From here, **Reports**, **Release QA**, and **Checklist** all become meaningful, since they aggregate the data from steps 3–6.

Everything else (Keyword Research, Domain Overview, Competitors, Backlinks, Redirects, Change Log, ranking snapshots, uptime checks) is independent and can be used any time, in any order.

---

## Module-by-module reference

### Dashboard (`/`)
Your daily landing page. KPI tiles (site count, tracked keywords, keywords ranking top 10, open alerts, 30-day uptime %, audit count) link out to their respective pages. Below that: a health card per website (uptime dot, latest audit score, keyword count) and two recent-activity tables (audits, uptime checks). Nothing is entered here — it's read-only, aggregated from every other module.

### Websites (`/websites`)
The root of everything. Add a website with a name and base URL; optionally set a GSC property string (`sc-domain:example.com`), industry, 2-letter target country, an alert email, and a crawl frequency in hours (used by the automatic hourly scheduler to decide when a site is "due" for a crawl). Each card has a "⋯" menu to edit or delete inline. Click into a site for its detail page.

### Website detail (`/websites/{id}`)
The control panel for one site:
- **Update Website** — edit the same settings from the add form.
- **Latest Monitoring** — a snapshot of the most recent uptime check, audit, crawl, and open-alert count, each linking to the full record.
- **Keywords** — add keywords to track (term, optional target URL, search engine, location, device, priority 1–3). Each row supports **Log ranking** (record a real SERP position you observed manually — position, search volume, difficulty, SERP features like "featured snippet"), **Edit**, and **Delete**.
- **Recent Uptime Checks** — **Record check** logs a manual up/down observation (status code, response time, notes). There's no automatic uptime pinger built in — you (or an external monitor you paste results from) are the source of truth here.

### GSC (`/gsc`)
Paste rows exported from Google Search Console. Two formats are auto-detected by column count:
- **5 columns** (chart export): `date,clicks,impressions,ctr,position`
- **7 columns** (detailed/page export): `date,query,page_url,clicks,impressions,ctr,avg_position`

CTR can be a plain decimal or a `%` value — both are normalized. This is the single most important manual data-entry step in the app: it powers Content Decay, the traffic-drop alert, and Reports.

### Domain Overview (`/domain-overview`)
Log periodic domain-level metrics you get from a third-party tool (estimated traffic, organic keyword count, referring domains, total backlinks, a 0–100 visibility index, average position). Each entry is a dated snapshot; the page plots them as a trend so you can see whether your domain metrics are moving up or down over time.

### Keyword Research (`/keyword-research`)
A keyword idea database, separate from the keywords you're actively tracking. Add ideas one at a time or bulk-paste CSV rows (`keyword,volume,kd,cpc,intent`). Filter by website, text search, country, intent, min volume, max difficulty. The **Track** button promotes a promising idea into a real tracked `Keyword` on the associated website (you'll then log its ranking position from the website detail page).

### Competitors / Keyword Gap (`/competitors`)
Add named competitors for a website, then log their keyword ranking snapshots (keyword, date checked, position, search volume) as you find them manually. The **Keyword Gap** table is computed automatically: it finds keywords where a competitor ranks in the top 30 and you either don't rank at all or rank worse, sorted by opportunity (search volume, then how well the competitor ranks).

### Backlinks (`/backlinks`)
Log backlinks you've found (source URL, target URL, anchor text, source authority score, nofollow/toxic flags, found/last-seen dates). Filter to toxic-only or nofollow-only. Stats tiles show totals, toxic count, nofollow count, and average authority. No live backlink-discovery API — this is a place to track links you've already found elsewhere.

### Technical (`/technical`)
The one page that does real crawling. **Run crawl** fetches your site's `robots.txt` and `sitemap.xml` live, then crawls up to N pages (default 30, max 200) found in the sitemap (falling back to just the homepage if no sitemap exists). For each page it checks: title, canonical tag, meta robots, H1 count, word count, internal link count, URL depth, `hreflang` count, charset, AMP signal, and whether the page is indexable (200 status, not noindex, canonical doesn't point elsewhere) or orphaned (zero internal links pointing to it, and it isn't the homepage). After crawling, it automatically generates **internal link opportunity** suggestions (well-linked pages that should link to strong-content pages currently getting little internal linking). If a site is unreachable, the run is marked `failed` with the error recorded, and it won't block crawling your other sites. Click into a past run for its full page table and generated link suggestions.

### Link Opportunities (`/link-opportunities`)
Read-only output of the crawler's auto-generated internal linking suggestions for the selected website — nothing to configure here, just re-run a Technical crawl to refresh them.

### Content Decay (`/content-decay`)
Finds pages whose organic clicks (from imported GSC data) dropped 20% or more comparing the last 28 days to the 28 days before that, filtering out low-traffic noise (previous period needs at least 20 clicks to qualify). Requires GSC data — with none imported, this page is empty. Use it to prioritize which pages need a content refresh or new internal links.

### Alerts (`/alerts`)
A filterable feed (by website, severity, open/resolved) of automatically detected problems. **Run evaluation** checks every website for: orphan pages and non-indexable pages (from the latest Technical crawl), and traffic drops ≥30% on any page (from GSC data, same logic as Content Decay but a higher threshold and it's classified `high` severity). It won't create duplicate alerts for the same issue within 24 hours. This same evaluation also runs automatically every hour in the background — the button is for on-demand checks. **Resolve** marks an alert handled.

### Reports (`/reports`)
A one-page rollup per website: tracked keyword count, how many rank top 10, open alert count, 30-day uptime rate, latest crawl's indexable/orphan page counts, and the top 20 content-decay rows. **Export CSV** downloads the same data for sharing outside the app — useful for client or stakeholder reporting.

### Change Log (`/change-log`)
A manual audit trail. Log what you changed (area: content, metadata, technical, internal links, redirects, schema, or other), when, the old/new value, and an impact level (low/medium/high). Purely for historical record — nothing else in the app reads from it automatically, but it's invaluable when a ranking or traffic shift shows up later and you need to correlate it against what actually changed.

### Redirects (`/redirects`)
Maintain a table of redirect rules per website (from-path, destination URL, status code 301/302/307/308, active flag, notes). **Check** live-fetches the from-path on your real site (without following the redirect) and verifies it actually returns a 3xx with a `Location` header matching your configured destination — flagging mismatches, missing headers, or unexpected status codes. Failing checks feed into Release QA.

### Release QA (`/release-qa`)
A pre-deploy gate. Pick an environment (staging/production/preview) and optional release tag, and it scores the website against: crawl health (missing crawl data, orphan/non-indexable/broken pages from the latest Technical run), the latest on-page audit's score, open alert count, and any redirect rules currently failing their last check. Produces a 0–100 score and pass/warn/fail verdict, with every contributing issue listed. Run this before you ship changes to catch regressions.

### Checklist (`/checklist`)
An auto-graded 4-section SEO checklist (crawlability, on-page, technical, international) built from your latest crawl, latest audit, GSC data, and website settings — each item is pass/warn/fail with a specific reason (not a static list). **Generate tasks** converts every warn/fail item into a trackable task with a due date (3 days for fails, 7 for warnings) — avoids creating duplicates if one's already open. Mark tasks **done** as you fix them.

### Audits (`/audits`)
On-page SEO analysis for a single URL. **Run audit** either fetches the URL live or, if you paste raw HTML instead, analyzes that directly (useful for pages behind auth, or pre-production content). Checks: title length (35–60 chars ideal), meta description length (70–160 ideal), H1 count (exactly one ideal), canonical tag presence, images missing `alt` text, word count (300+ ideal), internal/external link counts. Produces a 0–100 score and pass/warn/fail status with an itemized issue list. If the URL can't be reached (DNs failure, timeout), you get a clear validation error instead of a crash.

### Profile
Standard account management — update your name/email, change your password, or delete your account. Not SEO-related; just user settings.

---

## What runs automatically vs. what you have to trigger

**Automatic (hourly, via the server's cron scheduler):**
- `seo:run-scheduled` — crawls every website whose `next_crawl_at` is due (based on each site's configured crawl frequency)
- `seo:evaluate-alerts` — runs the same alert evaluation as the "Run evaluation" button, for every website

**Manual only (you have to click a button or submit a form):**
- Everything else. GSC imports, domain snapshots, competitor tracking, backlinks, redirect checks, audits, release QA runs, checklist task generation, ranking snapshots, and uptime checks all require you to actively enter or trigger them. There is no live GSC API connection, no live backlink-discovery integration, and no automatic uptime pinger — those are all "bring your own data" by design.

---

## Known gaps

- **No automated tests** exist for any controller (just Pest scaffolding). Verifying changes currently means manual testing.
- **Domain Overview, Competitors, Backlinks** trend data is only as good as how consistently you log snapshots — there's no scheduled reminder to keep it current.
- Production runs PostgreSQL, local dev runs SQLite — a few subtle SQL differences have bitten this app before (see `PLAN.md` and `CLAUDE.md` for the specifics); worth keeping in mind if you extend any aggregate queries.
