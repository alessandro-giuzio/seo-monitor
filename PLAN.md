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

## Not started — remaining backlog from NEXT_STEPS.md, in priority order

### 3. Active nav link highlighting — MEDIUM
- File: `resources/views/components/layouts/app.blade.php` (nav links section, ~lines 18–40).
- Use `request()->routeIs('dashboard')` etc. per link to conditionally add `border-sky-400 text-sky-300` classes to the current page's link. All nav links follow the same repeated class pattern (`rounded-md border border-slate-700 px-3 py-1.5 hover:border-sky-400 hover:text-sky-300`), so this is a per-`<a>` conditional class tweak, not a structural change.

### 4. Flash message toasts — MEDIUM
- File: `resources/views/components/layouts/app.blade.php` (add banner after `<header>`).
- Session already sets `status` flash on redirects (see `SeoAuditController@store` and others using `->with('status', ...)`) but nothing renders it in the authenticated layout. Add a dismissible banner reading `session('status')` (success/emerald) and `session('error')` (danger/red) — Alpine.js `x-data`/`x-show` for dismiss, consistent with the toggle pattern already used elsewhere.

### 5. Empty states on list pages — MEDIUM
- Files: `resources/views/backlinks/index.blade.php`, `competitors/index.blade.php`, `alerts/index.blade.php`, `keyword-research/index.blade.php`.
- Add a message + CTA inside each `@forelse`/`@empty` block (audits/index.blade.php already has a minimal one-line empty state — that's the low bar; these should include a link/button to add the first item).

### 6. Website edit/delete from index page — LOW
- File: `resources/views/websites/index.blade.php`.
- Add an Alpine.js dropdown ("⋯" menu) per card with Edit/Delete, instead of requiring a click into `/websites/{id}` first.

### 7. Keyword inline edit — LOW
- File: `resources/views/websites/show.blade.php`.
- Add inline edit toggle on keyword rows (currently delete-only).

## Verification checklist for next session
- `npm run dev` running, Herd serving `http://seo-demo.test`.
- Login: giuzio@icloud.com / password (per `NEXT_STEPS.md`).
- After each item: visually check the affected page in-browser, and for anything touching flash/session state, do a real form submit (not just a page load) to confirm behavior.
