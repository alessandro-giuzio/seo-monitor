# How to use the SEO Toolkit

Task-by-task checklists for running SEO monitoring for clients with this app (https://seo.xrun.gdn) and the client portal (https://apps.xrun.gdn). For what each page does in detail, see [FUNCTIONALITY.md](FUNCTIONALITY.md).

**What the app does on its own:** checks uptime every 5 minutes, syncs Search Console and logs keyword rankings every night, re-crawls sites on their schedule, audits each homepage every Monday and on the last day of the month, and looks for new alerts every hour. **Your job:** set clients up properly, look at what it finds, act on it, and publish the monthly report.

> Email is currently paused. Alerts and the monthly data check are not emailed to you, so the weekly and month-end routines below include checking them by hand.

---

## 1. Onboard a new client (about 20 minutes)

**In seo-monitor:**

1. **Websites → Add website.**
   - Name and base URL (the homepage, e.g. `https://client.com`). Uptime checks and the automatic audits use this exact URL.
   - **GSC property**: `sc-domain:client.com` (domain property) or `https://client.com/` (URL-prefix property), exactly as in Search Console.
   - Target country, industry, crawl frequency (24 h is fine).
   - Alert email: fill it in now, even with email paused, so it works once email is turned on.
2. **GSC → Connect Google Search Console** for that website, and authorize with the Google account that has access to the client's property. Then click **Sync now** once, so you don't have to wait for tonight.
3. **Website detail page → Keywords.** Add 5–20 keywords that matter to the client.
   - Write each term **exactly as people search it** (`dentist barcelona`, not `Dentist – Barcelona`). Rankings come from Search Console only when the term matches a real search query (capitalisation doesn't matter).
   - Not sure what people type? Look at the queries on the GSC page for that site and copy them.
   - Add the target URL and set priority 1 for the keywords the client cares about most.
4. **Technical → Run crawl.** Check that it completes, and note any non-indexable or orphan pages.
5. **Audits → Run audit** for the 2–3 most important pages besides the homepage (main service page, contact page). **Always select the website** in the form.
6. **Alerts → Run evaluation**, then go through what it found (see section 5).
7. Note the **website id**: the number in the page URL `/websites/{id}`.

**In the portal (apps.xrun.gdn, admin):**

8. Open the client → **Client settings → seo-monitor link**, enter the website id, then **Save link**.
9. Reload the client's admin page and check that the status card shows uptime, audit score and keywords. The client sees the same card on their dashboard.

**The next day:** open the website page in seo-monitor. Keywords that people search for should now show a position with a green **GSC** badge. Keywords still showing `-` either get no Google impressions yet or don't match a real query: rename them to the real query, or log their position by hand (**Log ranking**).

---

## 2. Weekly routine (about 10–15 minutes, e.g. Monday)

1. **Dashboard.** Scan the health cards: any red uptime dot, a falling audit score, or a jump in open alerts?
2. **Alerts** (filter: open). For each alert, fix it or decide it's fine, then click **Resolve** (see section 5 for what to do).
3. **Content Decay**, for each client. Pages losing 20%+ of their clicks are your content-refresh list: update the content, add internal links from strong pages, and check the title tag is still competitive.
4. **Link Opportunities.** Pick 2–3 suggestions and add those internal links on the client's site.
5. **Checklist → Generate tasks** if you haven't already, then work on the open tasks and mark them **done**.
6. Changed something on a client site? Log it in **Change Log** (what changed, date, old and new value). When traffic moves next month, this is how you'll know why.

---

## 3. Month end and the client report

The portal creates a **draft** report for every linked client on the 1st of the month at 06:00 (for the previous month). It uses what seo-monitor has for that month: uptime %, incidents, audit score (this month and last month), and keyword positions at the start and end of the month.

**Around the 28th:**

1. Check what's missing. Coolify → seo-monitor → Terminal:
   ```bash
   php artisan seo:check-report-data --no-mail
   ```
   It prints each website as `ready` or with a list of problems.
2. Fix what it lists:
   - *No / too few uptime checks.* The automatic checks aren't running: see section 5, "Uptime checks stopped".
   - *No audit this month.* Run one on the homepage with the website selected. (The automatic one also runs on the last day of the month.)
   - *Keywords with no ranking this month.* Either rename them to match real search queries, or **Log ranking** by hand with the position you see on Google.
   - *Search Console not connected / not synced.* Reconnect on the GSC page and click **Sync now**.

**On the 1st or 2nd:**

3. Portal → **Reports → Drafts to publish.** Open each draft and check the numbers make sense.
4. If you fixed data in seo-monitor after the draft was made, click **Regenerate data**.
5. Write the **Summary for the client**: a few sentences on what you did this month (use the Change Log) and what comes next. Then **Save summary**.
6. **Publish and notify the client.** The client gets an email and can see the report. To fix a published report, **Unpublish**, **Regenerate data**, then publish again.

---

## 4. Before a client site release (redesign, migration, big content change)

1. **Redirects.** Add a rule for every old URL that changes (old path → new URL, 301), then click **Check** on each one *after* the release is live.
2. **Technical → Run crawl** on the new site (or on staging, if it has its own website entry).
3. **Release QA → Run.** Choose the environment and a release tag (e.g. `v2-redesign`). Fix everything listed as **fail** before you call it done; read through each **warn**.
4. **Audits** of the key pages on the new version (paste the HTML if the page is behind a staging login).
5. **Change Log.** Log the release with impact *high*, so the next months' traffic changes can be read against it.
6. Watch **Alerts** and **Content Decay** closely for the next 2–4 weeks.

---

## 5. When something looks wrong

| You see | What to do |
| --- | --- |
| **Site down** (red dot, "DOWN" checks) | Open the site yourself. If it's really down, contact the host or client. If it's up, read the notes on the check: a timeout or DNS error there often means a short blip. Incidents appear in the monthly report. |
| **Uptime checks stopped** (newest check is old) | Coolify terminal: `php artisan seo:check-uptime`. If that works, the scheduler isn't running in the container: restart the app in Coolify. If it errors, the error tells you why. |
| **Traffic drop alert** / page in Content Decay | Check the page on Google for its main query: has a competitor overtaken it, or has the title or snippet changed? Look at the Change Log for something that changed on that page. Refresh the content and add internal links. |
| **Non-indexable pages alert** | Open the crawl run (Technical). Look for an accidental `noindex`, a canonical pointing elsewhere, or a non-200 status. Fix it on the site, then re-crawl. |
| **Orphan pages alert** | The page exists in the sitemap but nothing links to it. Link to it from a related page, or remove it from the sitemap if it shouldn't exist. |
| **Audit score dropped** | Open the audit and read the issue list: usually a changed title or meta description, a lost H1, or new images without alt text. |
| **Keyword shows `-` or never gets a GSC badge** | The term doesn't match a real query, or the site gets no impressions for it. Rename it to a query from the GSC page, or log the position by hand. |
| **GSC data stopped updating** | GSC page: reconnect the property (the Google authorization may have been revoked), then **Sync now**. |
| **Portal status card missing** | Check the client's seo-monitor website id in the portal, and that both apps have the same `PORTAL_API_TOKEN`. |

---

## Quick reference

| When | What | Where |
| --- | --- | --- |
| New client | Section 1 checklist | seo-monitor + portal admin |
| Weekly | Dashboard → Alerts → Content Decay → Link Opportunities → Checklist | seo-monitor |
| ~28th | `php artisan seo:check-report-data --no-mail` and fix the gaps | Coolify terminal |
| 1st–2nd | Review drafts, write summary, publish | Portal → Reports |
| Before releases | Redirects → Crawl → Release QA → Change Log | seo-monitor |
| Any time you change a client site | Log it | Change Log |

**Automatic schedule (UTC):** uptime every 5 min · crawls hourly when due · alerts hourly · GSC sync 03:00 · GSC rankings 03:30 · homepage audit Mon 04:00 and last day of month 20:00 · report data check 28th 08:00 · portal drafts reports 1st 06:00.
