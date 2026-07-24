# SEO Toolkit UI polish — status and next steps

## Context

There's a prioritized backlog in `NEXT_STEPS.md` (written 2026-05-18) with 7 items, ordered by priority. The project is functionally complete (27 views, all backend controllers done) — what's left is UI polish.

## Done (commit 51a9936)

### 1. Login/auth dark theme restyle — ✅ done
- Restyled shared components to the dark palette: `resources/views/components/input-label.blade.php`, `text-input.blade.php`, `primary-button.blade.php`, `auth-session-status.blade.php` (`text-emerald-400`), `input-error.blade.php` (`text-red-400`). Since `login.blade.php`, `forgot-password.blade.php`, and `reset-password.blade.php` all build on these components, this fixed all three pages at once.
- `resources/views/auth/login.blade.php`: reinstated the "Forgot your password?" link (previously commented out) with dark styling.
- `resources/views/auth/forgot-password.blade.php`: intro paragraph recolored to `text-slate-400`.
- Guest layout (`resources/views/layouts/guest.blade.php`) and nav logo (`resources/views/components/layouts/app.blade.php`, `application-logo.blade.php`) were already dark from prior uncommitted work — carried through as-is.
- Verified via curl: logged in as giuzio@icloud.com locally, confirmed dark styling renders on `/login` and `/forgot-password`.

### 2. "Run audit" button on `/audits` — ✅ done
- `app/Http/Controllers/SeoAuditController.php@index` now passes `$websites` (id/name, ordered) to the view.
- `resources/views/audits/index.blade.php` has a collapsible Alpine.js form (matching the `websites/index.blade.php` pattern): URL (required), website dropdown (optional), and a "more options" section for raw HTML / audited-at, posting to `route('audits.store')`.
- Verified end-to-end via curl: submitted a real audit, redirected to `audits/{id}` with "Audit completed" flash, confirmed row appears in the list. Test data cleaned up afterward.

### 3. Active nav link highlighting — ✅ done
- `resources/views/components/layouts/app.blade.php`: nav links now built from a `$navLinks` array (`[route name, routeIs pattern, label]`) rendered via `@foreach`, with `@class` conditionally applying `border-sky-400 text-sky-300 bg-sky-500/10` when `request()->routeIs($pattern)` matches (wildcard patterns like `technical.*` so sub-pages like `technical.runs.show` also highlight the parent link).
- Verified via curl on `/audits` and `/` (dashboard) — correct link highlighted on each; spot-checked remaining nav pages (websites, gsc, domain-overview, keyword-research, competitors, backlinks, technical, link-opportunities, alerts, reports, change-log, redirects, release-qa, checklist) all still return 200.

### 4. Flash message toasts — already done (pre-existing, not part of this backlog work)
- Turns out `resources/views/components/layouts/app.blade.php` already renders `session('status')` in an emerald banner and `$errors->any()` in a red banner inside `<main>` (found while working on item #3). No work needed here — NEXT_STEPS.md was stale on this item.

### 5. Empty states on list pages — ✅ done
- `resources/views/backlinks/index.blade.php`: backlinks table empty state now points to the "Add Backlink" form above.
- `resources/views/competitors/index.blade.php`: two empty states — competitors list (points to "Add Competitor" form) and the keyword-gap table (explains it needs a competitor + ranking snapshot first, since gap rows are computed, not manually added).
- `resources/views/alerts/index.blade.php`: empty state points to the "Run evaluation" button (alerts are generated, not manually added).
- `resources/views/keyword-research/index.blade.php`: empty state points to the add-idea form / bulk import / clearing filters.
- All styled consistently: centered text block, `text-sm font-medium text-slate-300` heading + `text-xs text-slate-500` subtext, in place of the old one-line `text-slate-500` message.
- Verified via curl: all four pages return 200 and render their respective empty-state copy.

### 6. Website edit/delete from index page — ✅ done
- `resources/views/websites/index.blade.php`: each card is now a `div` with its own `x-data="{ menu, editing }"` scope (previously the whole card was a single `<a>`). A "⋯" button (top-right, `@click.outside` to close) opens a dropdown with Edit and Delete.
- Edit toggles an inline form on the card itself (name + base_url, the two required fields) submitting `PUT` to `route('websites.update', $website)` — same route the full edit form on `websites/show.blade.php` already uses.
- Delete submits a `DELETE` form to `route('websites.destroy', $website)` with a `confirm()` guard, matching the pattern used on backlinks/competitors delete buttons.
- Verified end-to-end via curl: edited a website's name/URL and confirmed the change persisted, then created and deleted a scratch website to confirm the delete path works. No stray test data left behind.

### 7. Keyword inline edit — ✅ done
- Found while starting this item: `resources/views/websites/show.blade.php` actually had **no** add-keyword form and **no** delete button at all, despite `KeywordController@store`/`destroy` already existing — NEXT_STEPS.md's description ("only delete, no edit") was stale, same as item #4. So this ended up being add + edit + delete, not just edit.
- Added `KeywordController@update` (`app/Http/Controllers/KeywordController.php`) with the same validation rules as `store`, and registered `PUT /keywords/{keyword}` as `keywords.update` in `routes/web.php`.
- `resources/views/websites/show.blade.php`: added a collapsible "+ Add keyword" form (term, target_url, search_engine, location, device, priority) above the table. Each row now has its own `x-data="{ editing }"` scope with Edit/Delete buttons; Edit swaps the row for an inline form (term, search_engine, device, priority editable; target_url/location carried through as hidden fields since they weren't shown in the table) submitting `PUT` to `keywords.update`; Delete submits `DELETE` to `keywords.destroy` with a `confirm()` guard.
- Verified end-to-end via curl: created a scratch keyword, edited its term/engine/device/priority and confirmed the DB row updated, then deleted it and confirmed removal. No stray test data left behind.

## Backlog complete
All 7 items from `NEXT_STEPS.md` are done. Nothing outstanding from that list — future work would need a fresh pass to identify next priorities (or re-check NEXT_STEPS.md itself, since it turned out stale on items #4 and #7).

## Bugs found and fixed after the backlog (production incidents)

### Postgres GROUP BY error on `/reports` and `/content-decay` — ✅ fixed (commit c55d064)
- `Website::gscMetrics()` (`app/Models/Website.php:94`) has a default `orderByDesc('metric_date')` on the relation. `ContentDecayController::index()` and `ReportController::buildReportData()` both build an aggregate query on top of it (`groupBy('page_url')` + `SUM(clicks)`), and the inherited `ORDER BY metric_date` survived into the final SQL. SQLite tolerates this; PostgreSQL's strict mode rejects it (`42803`), which is what was 500ing both pages in production.
- Fixed by adding `->reorder()` before the aggregate query in both controllers. Confirmed via `php artisan tinker` in the Coolify production terminal (calling the controller method directly, bypassing HTTP) that this was the exact exception, then verified the fix locally (SQL no longer contains `ORDER BY`, both pages return 200) before pushing.
- Also ruled out during this investigation: production's `migrate:status` shows `websites`, `competitor_keyword_snapshots`, `crawl_runs`, `release_qa_runs` as "Pending" due to two early migration-file renames (commits `86e326a`, `852895c`) — Laravel tracks migrations by filename, so production's already-migrated tables under the old names look unrecognized. This is bookkeeping drift only (proved via FK dependents that already ran successfully), not missing schema — did not need fixing for this incident, but worth knowing about if a future migration change touches those tables.

### `/profile` 500 error — ✅ fixed (commit 4f36901)
- Leftover Breeze scaffolding: `resources/views/profile/partials/update-profile-information-form.blade.php` had a hidden form with `action="{{ route('verification.send') }}"` evaluated unconditionally (email verification was never enabled — `MustVerifyEmail` is commented out on `App\Models\User`), and `resources/views/profile/partials/update-password-form.blade.php` posted to `route('password.update')`, which was never registered as a route (the controller `App\Http\Controllers\Auth\PasswordController@update` existed but nothing pointed at it).
- Fix: added `Route::put('/password', [PasswordController::class, 'update'])->name('password.update')` inside the authenticated group in `routes/web.php`; removed the dead verification-resend form and its guarding `@if` block from `update-profile-information-form.blade.php` entirely (unreachable dead code, not just unrouted).
- Verified end-to-end via curl: `/profile` returns 200, submitted a real password change, confirmed login with the new password worked, then reverted the password back to `password` so local dev credentials stay unchanged.
- No migration/schema involved, no production DB risk.

## Dead Breeze scaffolding removed (commit pending)
While fixing `/profile`, found a broader set of Breeze leftovers that were never wired to any route and had zero references anywhere else in the codebase (confirmed via full-repo grep before deleting). Removed:
- Views: `welcome.blade.php`, `auth/register.blade.php`, `auth/confirm-password.blade.php`, `auth/verify-email.blade.php`, `layouts/app.blade.php` (old Breeze layout, distinct from the live `components/layouts/app.blade.php`), `layouts/navigation.blade.php`, `components/dropdown.blade.php`, `components/dropdown-link.blade.php`, `components/nav-link.blade.php`, `components/responsive-nav-link.blade.php`.
- Controllers: `Auth/RegisteredUserController.php`, `Auth/ConfirmablePasswordController.php`, `Auth/EmailVerificationPromptController.php`, `Auth/EmailVerificationNotificationController.php`, `Auth/VerifyEmailController.php`.
- Component class: `App\View\Components\AppLayout` (rendered the dead `layouts/app.blade.php`, itself never invoked via `<x-app-layout>` anywhere).
- Kept: `modal.blade.php`, `secondary-button.blade.php`, `danger-button.blade.php` — these looked like similar Breeze leftovers but are actually live, used by `profile/partials/delete-user-form.blade.php` (the delete-account confirmation modal on the real `/profile` page).
- Verified: `composer dump-autoload` clean, `route:list` still resolves (63 routes), and smoke-tested `/`, `/profile`, `/websites`, `/audits`, `/reports`, `/content-decay`, `/technical`, `/alerts`, `/checklist` all return 200 after deletion.

## Verification checklist for next session
- `npm run dev` running, Herd serving `http://seo-demo.test`.
- Login: giuzio@icloud.com / password (per `NEXT_STEPS.md`).
- After each item: visually check the affected page in-browser, and for anything touching flash/session state, do a real form submit (not just a page load) to confirm behavior.
