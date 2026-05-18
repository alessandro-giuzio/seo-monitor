# SEO Toolkit — Claude Code Instructions

## Project overview

Self-hosted SEO monitoring toolkit built with Laravel 12, Tailwind CSS 4, Alpine.js, and Vite 7.
Deployed on Coolify (Hetzner VPS) at https://seo.xrun.gdn. Auth-only — no public registration.

## Local development

```bash
npm run dev        # Vite dev server (hot reload CSS/JS)
# Laravel Herd serves PHP automatically
# Local URL: http://seo-demo.test
```

No `php artisan serve` needed — Herd handles it.

## Stack

- **PHP** 8.4 (required — composer.lock has Symfony 8.x which needs 8.4+)
- **Laravel** 12
- **Tailwind CSS** 4 via `@tailwindcss/vite` — uses `@import 'tailwindcss'` in `resources/css/app.css`, NOT `tailwind.config.js`
- **Alpine.js** 3 — for all frontend interactivity (dropdowns, form toggles, modals)
- **Database** — SQLite locally, PostgreSQL in production
- **Queue/cache/session** — database driver in production, sync locally

## Key conventions

### Views
- All authenticated views use `<x-layouts.app :title="'Page Title'">` — the dark layout at `resources/views/components/layouts/app.blade.php`
- Auth pages (login etc.) use `<x-guest-layout>` — needs restyle to dark theme (see NEXT_STEPS.md)
- Card style: `rounded-xl border border-slate-800 bg-slate-900/70 p-4`
- Input style: `rounded-md border border-slate-700 bg-slate-900 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none`
- Color palette: slate-950 background, sky-300/400 accents, emerald-400 success, red-400 danger, slate-400 muted text
- Forms toggle open with Alpine.js `x-show` + `x-transition` — see `resources/views/websites/index.blade.php` as the reference pattern

### Routes
- All app routes are in `routes/web.php` wrapped in `Route::middleware('auth')->group(...)`
- Auth routes (login/logout/password reset) are in `routes/auth.php`
- No registration route exists — users created manually via `php artisan tinker`

### Controllers
- Thin controllers — validation in the controller, business logic in `app/Services/`
- After store/destroy: redirect with `->with('status', 'Message.')` flash

### Database
- Migrations must be in correct timestamp order — foreign key tables before dependent tables
- Run `php artisan migrate` locally after pulling new migrations

## Deployment

Push to `main` — Coolify auto-deploys via the Dockerfile.

```bash
git add .
git commit -m "your message"
git push origin main
```

Coolify builds a multi-stage Docker image: Node (Vite build) → PHP 8.4-fpm-alpine + nginx + supervisord.
The entrypoint runs `php artisan migrate --force` before starting supervisord.

### Production commands
Run in Coolify → your app → Terminal tab:

```bash
php artisan tinker --execute="App\Models\User::create(['name'=>'Name','email'=>'email@example.com','password'=>bcrypt('password')])"
php artisan migrate:status
php artisan queue:restart
```

### Health check
https://seo.xrun.gdn/up → should return `{"status":"ok"}`

## What still needs work

See `NEXT_STEPS.md` in the project root for the full prioritised list. Top items:

1. Login page dark theme restyle (`resources/views/auth/login.blade.php`)
2. "Run audit" button on `/audits` page (`resources/views/audits/index.blade.php`)
3. Active nav link highlighting (`resources/views/components/layouts/app.blade.php`)
4. Flash message toasts in the main layout
5. Empty states on list pages

## Creating a new user (production)

```bash
# In Coolify terminal
php artisan tinker --execute="App\Models\User::create(['name'=>'Alessandro','email'=>'giuzio@icloud.com','password'=>bcrypt('your-password')])"
```
