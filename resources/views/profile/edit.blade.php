<x-layouts.app :title="'Profile - SEO Toolkit'">
    <h1 class="text-2xl font-semibold">Profile</h1>
    <p class="mt-1 text-sm text-slate-400">Manage your account details and password.</p>

    <div class="mt-6 space-y-6">
        <div class="rounded-xl border border-slate-800 bg-slate-900/70 p-6">
            @include('profile.partials.update-profile-information-form')
        </div>

        <div class="rounded-xl border border-slate-800 bg-slate-900/70 p-6">
            @include('profile.partials.update-password-form')
        </div>

        <div class="rounded-xl border border-slate-800 bg-slate-900/70 p-6">
            @include('profile.partials.delete-user-form')
        </div>
    </div>
</x-layouts.app>
