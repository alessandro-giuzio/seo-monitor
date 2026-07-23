<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'SEO Toolkit') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-950 font-sans text-slate-100 antialiased">
        <div class="flex min-h-screen flex-col items-center justify-center px-4">

            <a href="/" class="mb-8 flex flex-col items-center gap-3">
                <x-application-logo class="h-14 w-14" />
                <div class="text-center">
                    <span class="block text-xl font-semibold tracking-tight text-slate-100">SEO Toolkit</span>
                    <span class="block text-xs text-slate-400">Research + Monitoring</span>
                </div>
            </a>

            <div class="w-full max-w-md rounded-xl border border-slate-800 bg-slate-900/70 px-8 py-7 shadow-xl">
                {{ $slot }}
            </div>

        </div>
    </body>
</html>
