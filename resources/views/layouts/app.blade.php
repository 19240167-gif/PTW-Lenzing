<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config("app.name") }}</title>
    @vite(["resources/css/app.css", "resources/js/app.js"])
</head>
<body class="h-full bg-[#F3F4F6] text-[#333333] font-sans antialiased">

{{-- Top Navigation --}}
<nav class="bg-[#00843D] text-white shadow-sm fixed top-0 left-0 right-0 z-30 h-14 flex items-center px-4">
    <div class="flex items-center gap-3 flex-1">
        <span class="font-bold tracking-wide text-base">PTW Portal</span>
        <span class="text-green-200 text-xs hidden sm:inline">Permit to Work Management</span>
    </div>
    <div class="flex items-center gap-3" x-data="{ open: false }">
        <span class="text-sm text-green-100 hidden sm:inline">{{ auth()->user()->name ?? '' }}</span>
        <button @click="open = !open"
                class="flex items-center gap-2 bg-[#006B32] hover:bg-[#005428] rounded px-3 py-1.5 text-sm transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
            </svg>
            <span class="font-medium uppercase tracking-wide text-xs">
                {{ strtoupper(auth()->user()->username ?? 'USER') }}
            </span>
        </button>
        <div x-show="open" @click.outside="open = false"
             class="absolute right-4 top-14 bg-white border border-[#D1D5DB] rounded shadow-md w-44 py-1 z-50" x-cloak>
            <form method="POST" action="{{ route("auth.logout") }}">
                @csrf
                <button type="submit" class="w-full text-left block px-4 py-2 text-sm text-[#333333] hover:bg-[#F3F4F6]">
                    Sign Out
                </button>
            </form>
        </div>
    </div>
</nav>

<div class="flex pt-14 min-h-full">

    {{-- Sidebar --}}
    <aside class="w-52 bg-white border-r border-[#D1D5DB] fixed top-14 bottom-0 left-0 z-20 overflow-y-auto">
        <nav class="py-3">

            <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                <x-slot:icon>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0h6"/>
                </x-slot:icon>
                Dashboard
            </x-nav-link>

            <x-nav-link :href="route('permits.index')" :active="request()->routeIs('permits.*')">
                <x-slot:icon>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </x-slot:icon>
                Work Permits
            </x-nav-link>

            <x-nav-link :href="route('approvals.index')" :active="request()->routeIs('approvals.*')">
                <x-slot:icon>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </x-slot:icon>
                My Approvals
            </x-nav-link>

            @if(auth()->user()?->is_global_admin)
            <div class="mt-4 px-4 mb-1">
                <span class="text-[10px] font-semibold uppercase tracking-widest text-[#6B7280]">Admin</span>
            </div>
            <x-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">
                <x-slot:icon>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-5-3.87M9 20H4v-2a4 4 0 015-3.87m6-4a4 4 0 11-8 0 4 4 0 018 0z"/>
                </x-slot:icon>
                Users
            </x-nav-link>
            <x-nav-link :href="route('admin.audit.index')" :active="request()->routeIs('admin.audit.*')">
                <x-slot:icon>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                </x-slot:icon>
                Audit Log
            </x-nav-link>
            @endif

        </nav>
    </aside>

    {{-- Main Content --}}
    <main class="flex-1 ml-52 p-6 min-w-0">
        {{ $slot }}
    </main>

</div>

</body>
</html>
