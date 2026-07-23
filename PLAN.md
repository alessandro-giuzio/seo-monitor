# Resume SEO Toolkit UI polish — finish items #1 and #2 from NEXT_STEPS.md

## Context

There's already a prioritized backlog in `NEXT_STEPS.md` (written 2026-05-18) with 7 items, ordered by priority. The project is functionally complete (27 views, all backend controllers done) — what's left is UI polish. Checking current state:

- **Item #1 (login dark theme restyle) is partially done but uncommitted.** `git status` shows in-progress, unstaged changes to `resources/views/auth/login.blade.php`, `resources/views/components/application-logo.blade.php` (new magnifying-glass logo), `resources/views/components/layouts/app.blade.php` (logo added to nav), and `resources/views/layouts/guest.blade.php` (now dark: slate-950 bg, bordered card, new logo). This is good, on-pattern work — just incomplete:
  - `forgot-password.blade.php` and `reset-password.blade.php` still use light Breeze markup (`text-gray-600`, `bg-white`, unstyled inputs) even though they already inherit the now-dark `<x-guest-layout>` — so on those two pages the surrounding chrome is dark but the form content is still styled light.
  - In `login.blade.php`, the "Forgot your password?" link was commented out rather than restyled — this needs to be reinstated and restyled, not left disabled, since the forgot-password flow itself exists and works (`MAIL_MAILER=log` — reset links go to the log file locally, which is fine for a self-hosted tool).
- **Item #2 (Run audit button) is not started.** `resources/views/audits/index.blade.php` has no form. Confirmed `SeoAuditController@store` (app/Http/Controllers/SeoAuditController.php:33) accepts `website_id` (nullable, exists:websites,id), `url` (required, url), `raw_html` (nullable), `audited_at` (nullable) — matches NEXT_STEPS.md exactly. `SeoAuditController@index` currently only passes `$audits` to the view — needs `$websites` added for the dropdown.

This plan covers finishing items #1 and #2, since #1 is mid-flight and #2 is next in priority order. Items #3–7 (nav active state, toasts, empty states, website card menu, keyword inline edit) are lower priority and not included here — flag them as follow-up once these two land.

## Plan

### 1. Finish login dark theme restyle

- `resources/views/auth/login.blade.php`: uncomment the "Forgot your password?" link, restyle it to match the dark theme (`text-slate-400 hover:text-sky-300` in place of the gray classes), keep it inline with the existing submit button layout.
- `resources/views/auth/forgot-password.blade.php`: restyle the intro paragraph (`text-slate-400` instead of `text-gray-600`) and match existing dark-form conventions already used elsewhere (e.g. `resources/views/websites/index.blade.php`) — labels, inputs already come from `x-input-label`/`x-text-input` components, so check whether those components themselves need dark variants (`resources/views/components/input-label.blade.php`, `resources/views/components/text-input.blade.php`) rather than restyling every page individually. If those shared components are still light-themed, fix them once there — it'll fix both forgot-password and reset-password pages simultaneously.
- `resources/views/auth/reset-password.blade.php`: same treatment — should fall out mostly free once the shared `x-input-label`/`x-text-input`/`x-primary-button` components are dark-themed.
- Verify `x-auth-session-status` and `x-input-error` components also render legibly on dark background (likely need `text-emerald-400` / `text-red-400` instead of default green-600/red-600).

### 2. Add "Run audit" button to `/audits`

- `app/Http/Controllers/SeoAuditController.php@index`: add `Website::orderBy('name')->get(['id', 'name'])` (or similar) to the view data as `$websites`.
- `resources/views/audits/index.blade.php`: add an Alpine.js-toggled form above the table, following the exact pattern in `resources/views/websites/index.blade.php` (x-data open/close toggle, same input classes: `rounded-md border border-slate-700 bg-slate-900 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none`). Fields:
  - `website_id` — a `<select>` populated from `$websites`, optional.
  - `url` — required text input, type `url`.
  - Optional collapsible "more options" section for `raw_html` (textarea) and `audited_at` (datetime-local), mirroring the More Options pattern already in websites/index.blade.php.
  - Posts to `route('audits.store')`.

## Verification

- Local: `npm run dev` + visit `http://seo-demo.test/login`, `/forgot-password`, and follow a reset link (check `storage/logs/laravel.log` for the mailed link since `MAIL_MAILER=log`) to confirm all three auth pages are fully dark-themed with no light-styled leftovers.
- Visit `/audits`, use the new form to submit a URL with and without selecting a website, confirm a new row appears and `audits.show` renders correctly.
- Run `php artisan route:list --name=password` and `--name=audits` to confirm no route changes were needed (this is a views/controller-data-only change).
