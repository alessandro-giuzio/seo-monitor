<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Privacy Policy — SEO Toolkit</title>

        @vite(['resources/css/app.css'])
    </head>
    <body class="min-h-screen bg-slate-950 font-sans text-slate-100 antialiased">
        <div class="mx-auto max-w-3xl px-4 py-12">

            <a href="{{ route('login') }}" class="mb-10 flex items-center gap-3">
                <x-application-logo class="h-10 w-10" />
                <div>
                    <span class="block text-lg font-semibold tracking-tight text-slate-100">SEO Toolkit</span>
                    <span class="block text-xs text-slate-400">Research + Monitoring</span>
                </div>
            </a>

            <article class="space-y-8 rounded-xl border border-slate-800 bg-slate-900/70 p-6 text-sm leading-relaxed text-slate-300 sm:p-8">
                <header class="space-y-2">
                    <h1 class="text-2xl font-semibold text-slate-100">Privacy Policy</h1>
                    <p class="text-slate-400">Last updated: 20 October 2026</p>
                </header>

                <section class="space-y-2">
                    <h2 class="text-base font-semibold text-sky-300">Who runs this tool</h2>
                    <p>
                        SEO Toolkit (seo.xrun.gdn) is run by Alessandro Giuzio, trading as Agency-GA
                        (<a href="https://www.agencyga.es" class="text-sky-400 underline hover:text-sky-300">www.agencyga.es</a>,
                        <a href="https://www.giuz.io" class="text-sky-400 underline hover:text-sky-300">www.giuz.io</a>).
                        Contact: <a href="mailto:maybeitry@gmail.com" class="text-sky-400 underline hover:text-sky-300">maybeitry@gmail.com</a>.
                    </p>
                </section>

                <section class="space-y-2">
                    <h2 class="text-base font-semibold text-sky-300">What it is</h2>
                    <p>
                        An internal SEO monitoring tool. It is used only by its operator, for Agency-GA's own websites and the
                        websites of Agency-GA's clients. There is no public sign-up; accounts are created by the operator.
                    </p>
                </section>

                <section class="space-y-2">
                    <h2 class="text-base font-semibold text-sky-300">Google data we access</h2>
                    <p>
                        When a website is connected to Google Search Console, the tool requests only the read-only scope
                        <code class="rounded bg-slate-800 px-1 text-slate-200">https://www.googleapis.com/auth/webmasters.readonly</code>.
                        For the Search Console properties the connected Google account has access to, it reads search
                        performance data: date, search query, page URL, clicks, impressions, click-through rate and average position.
                    </p>
                    <p>It has no write access to Search Console and does not access any other Google service.</p>
                </section>

                <section class="space-y-2">
                    <h2 class="text-base font-semibold text-sky-300">How the data is used</h2>
                    <p>
                        Search Console data is used only to provide the tool's features: SEO reports, trends, content-decay
                        and traffic-drop alerts, and keyword positions for tracked keywords. A small summary for a client's own
                        website (such as the positions of its tracked keywords) is shown to that client in Agency-GA's client
                        portal, which is run by the same operator.
                    </p>
                    <p>
                        The data is not sold, not shared with other third parties, not used for advertising, and not used to
                        train AI or machine-learning models.
                    </p>
                    <p>
                        SEO Toolkit's use and transfer of information received from Google APIs adheres to the
                        <a href="https://developers.google.com/terms/api-services-user-data-policy" class="text-sky-400 underline hover:text-sky-300">Google API Services User Data Policy</a>,
                        including the Limited Use requirements.
                    </p>
                </section>

                <section class="space-y-2">
                    <h2 class="text-base font-semibold text-sky-300">Storage and security</h2>
                    <p>
                        All data is stored in the tool's own database on a self-hosted server (a virtual server rented from
                        Hetzner), served over HTTPS. Google OAuth access and refresh tokens are kept on the server only, never
                        sent to the browser, and are stored encrypted (AES-256) with the application's secret key.
                    </p>
                </section>

                <section class="space-y-2">
                    <h2 class="text-base font-semibold text-sky-300">Retention, revoking access and deletion</h2>
                    <p>
                        Access can be revoked at any time in two ways:
                    </p>
                    <ul class="list-disc space-y-1 pl-5">
                        <li>
                            The <strong class="text-slate-100">Disconnect</strong> button on the tool's Search Console page deletes the
                            stored access and refresh tokens for that website, so the tool can no longer fetch new data.
                        </li>
                        <li>
                            At <a href="https://myaccount.google.com/permissions" class="text-sky-400 underline hover:text-sky-300">myaccount.google.com/permissions</a>,
                            which revokes the tool's access on Google's side. Disconnect only deletes the tokens stored in the
                            tool, so use this page as well to revoke the grant itself.
                        </li>
                    </ul>
                    <p>
                        Search Console data that was already imported stays in the tool after disconnecting, so past reports keep
                        working. It is deleted, together with the keyword positions derived from it, when the website is removed
                        from the tool. To have imported data deleted, email
                        <a href="mailto:maybeitry@gmail.com" class="text-sky-400 underline hover:text-sky-300">maybeitry@gmail.com</a>.
                    </p>
                </section>

                <section class="space-y-2">
                    <h2 class="text-base font-semibold text-sky-300">Cookies</h2>
                    <p>
                        Only the essential cookies needed to log in: a session cookie, a CSRF-protection cookie
                        (<code class="rounded bg-slate-800 px-1 text-slate-200">XSRF-TOKEN</code>), and a "remember me" cookie if
                        that box is ticked at login. There are no tracking, advertising or analytics cookies or scripts.
                    </p>
                </section>

                <section class="space-y-2">
                    <h2 class="text-base font-semibold text-sky-300">Changes</h2>
                    <p>If this policy changes, the new version is published on this page with a new "Last updated" date.</p>
                </section>
            </article>

        </div>
    </body>
</html>
