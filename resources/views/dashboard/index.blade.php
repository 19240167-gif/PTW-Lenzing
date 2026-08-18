<x-layouts.app>
<x-slot:title>Dashboard — PTW Portal</x-slot:title>

<x-page-header title="Dashboard" description="Selamat datang, {{ auth()->user()->name }}" />

{{-- Stats --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <x-card class="px-4 py-4">
        <p class="text-[10px] font-semibold uppercase tracking-widest text-[#6B7280]">My Permits</p>
        <p class="text-3xl font-bold text-[#333333] mt-1">{{ $stats['my_permits'] }}</p>
        <p class="text-xs text-[#6B7280] mt-0.5">Total work permits</p>
    </x-card>

    <a href="{{ route('approvals.index') }}">
        <x-card class="px-4 py-4 border-l-4 border-l-amber-400 hover:bg-[#E8F5EE] transition">
            <p class="text-[10px] font-semibold uppercase tracking-widest text-[#6B7280]">Pending Approval</p>
            <p class="text-3xl font-bold text-amber-500 mt-1">{{ $stats['pending_approval'] }}</p>
            <p class="text-xs text-[#6B7280] mt-0.5">Menunggu persetujuan saya</p>
        </x-card>
    </a>

    <x-card class="px-4 py-4 border-l-4 border-l-[#00843D]">
        <p class="text-[10px] font-semibold uppercase tracking-widest text-[#6B7280]">Active Permits</p>
        <p class="text-3xl font-bold text-[#00843D] mt-1">{{ $stats['active_permits'] }}</p>
        <p class="text-xs text-[#6B7280] mt-0.5">Sedang berjalan</p>
    </x-card>

    <x-card class="px-4 py-4">
        <p class="text-[10px] font-semibold uppercase tracking-widest text-[#6B7280]">My Releases</p>
        <p class="text-3xl font-bold text-[#333333] mt-1">{{ $stats['my_releases'] }}</p>
        <p class="text-xs text-[#6B7280] mt-0.5">Ditugaskan sebagai release</p>
    </x-card>
</div>

{{-- Recent Permits --}}
<x-card class="mb-4">
    <div class="px-4 py-3 flex items-center justify-between border-b border-[#D1D5DB]">
        <span class="text-[10px] font-semibold uppercase tracking-widest text-[#6B7280]">Work Permit Terbaru</span>
        <a href="{{ route('permits.index') }}" class="text-xs text-[#00843D] hover:underline">Lihat semua →</a>
    </div>

    @if($recentPermits->isEmpty())
    <div class="px-4 py-8 text-center text-sm text-[#6B7280]">
        Belum ada work permit.
        <a href="{{ route('permits.create') }}" class="text-[#00843D] hover:underline ml-1">Buat sekarang →</a>
    </div>
    @else
    <div class="divide-y divide-[#D1D5DB]">
        @foreach($recentPermits as $permit)
        <a href="{{ route('permits.show', $permit) }}"
           class="flex items-center gap-3 px-4 py-3 hover:bg-[#E8F5EE] transition">
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2">
                    <span class="font-mono text-xs font-semibold text-[#00843D]">{{ $permit->permit_number }}</span>
                    <x-status-badge :status="$permit->getStatusEnum()->value" />
                </div>
                <p class="text-sm text-[#333333] mt-0.5 truncate">{{ $permit->title }}</p>
                <p class="text-xs text-[#6B7280]">{{ $permit->plant?->name }} {{ $permit->building ? '— '.$permit->building->name : '' }}</p>
            </div>
            <span class="text-xs text-[#6B7280] shrink-0">
                {{ $permit->updated_at->timezone('Asia/Jakarta')->format('d M') }}
            </span>
        </a>
        @endforeach
    </div>
    @endif
</x-card>

{{-- Quick action --}}
<div>
    <x-btn href="{{ route('permits.create') }}">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        Buat Work Permit Baru
    </x-btn>
</div>

</x-layouts.app>
