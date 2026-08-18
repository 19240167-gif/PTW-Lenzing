<x-layouts.app>
<x-slot:title>Work Permits — PTW Portal</x-slot:title>

<div class="flex items-start justify-between mb-6">
    <div>
        <h1 class="text-xl font-semibold text-[#333333]">Work Permits</h1>
        <p class="text-sm text-[#6B7280] mt-0.5">Daftar Ijin Kerja</p>
    </div>
    <x-btn href="{{ route('permits.create') }}">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        Buat PTW Baru
    </x-btn>
</div>

@if(session('success'))
    <div class="mb-4 flex items-center gap-2 bg-[#E8F5EE] border border-[#00843D] text-[#006B32] rounded px-3 py-2 text-sm">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        {{ session('success') }}
    </div>
@endif

{{-- Filters --}}
<x-card class="mb-4 px-4 py-3">
    <form method="GET" action="{{ route('permits.index') }}" class="flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-[160px]">
            <label class="block text-xs font-medium text-[#6B7280] mb-1">Cari</label>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="No. PTW atau judul..."
                   class="w-full border border-[#D1D5DB] rounded px-2.5 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-[#00843D] focus:border-[#00843D]">
        </div>
        <div class="min-w-[140px]">
            <label class="block text-xs font-medium text-[#6B7280] mb-1">Plant</label>
            <select name="plant_id" class="w-full border border-[#D1D5DB] rounded px-2.5 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-[#00843D]">
                <option value="">Semua Plant</option>
                @foreach($plants as $plant)
                    <option value="{{ $plant->id }}" @selected(request('plant_id') == $plant->id)>{{ $plant->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="min-w-[140px]">
            <label class="block text-xs font-medium text-[#6B7280] mb-1">Status</label>
            <select name="status" class="w-full border border-[#D1D5DB] rounded px-2.5 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-[#00843D]">
                <option value="">Semua Status</option>
                @foreach($statuses as $s)
                    <option value="{{ $s->value }}" @selected(request('status') === $s->value)>{{ $s->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex gap-2">
            <x-btn type="submit" variant="primary">Filter</x-btn>
            <x-btn href="{{ route('permits.index') }}" variant="secondary">Reset</x-btn>
        </div>
    </form>
</x-card>

{{-- Table --}}
<x-card>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-[#F3F4F6] border-b border-[#D1D5DB] text-left text-xs font-semibold text-[#6B7280] uppercase tracking-wide">
                    <th class="px-4 py-2.5">Permit</th>
                    <th class="px-4 py-2.5">Plant</th>
                    <th class="px-4 py-2.5">Building</th>
                    <th class="px-4 py-2.5">Approver</th>
                    <th class="px-4 py-2.5">Released By</th>
                    <th class="px-4 py-2.5">Valid From</th>
                    <th class="px-4 py-2.5">Valid Until</th>
                    <th class="px-4 py-2.5">Status</th>
                    <th class="px-4 py-2.5">Modified</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#D1D5DB]">
                @forelse($permits as $permit)
                @php
                    $approverAssignment = $permit->activeAssignments->where('assignment_type->value', 'approver')->first()
                        ?? $permit->activeAssignments->firstWhere('assignment_type', \App\Enums\AssignmentType::APPROVER);
                    $releaseAssignment  = $permit->activeAssignments->firstWhere('assignment_type', \App\Enums\AssignmentType::RELEASE);
                @endphp
                <tr class="hover:bg-[#E8F5EE] transition cursor-pointer" onclick="window.location='{{ route('permits.show', $permit) }}'">
                    <td class="px-4 py-2.5">
                        <a href="{{ route('permits.show', $permit) }}"
                           class="font-mono font-semibold text-[#00843D] hover:underline text-xs">
                            {{ $permit->permit_number }}
                        </a>
                        <p class="text-[#333333] text-sm mt-0.5 truncate max-w-[180px]">{{ $permit->title }}</p>
                    </td>
                    <td class="px-4 py-2.5 text-[#333333]">{{ $permit->plant?->name ?? '—' }}</td>
                    <td class="px-4 py-2.5 text-[#333333]">{{ $permit->building?->name ?? '—' }}</td>
                    <td class="px-4 py-2.5 text-[#6B7280]">{{ $approverAssignment?->user?->name ?? '—' }}</td>
                    <td class="px-4 py-2.5 text-[#6B7280]">{{ $releaseAssignment?->user?->name ?? '—' }}</td>
                    <td class="px-4 py-2.5 text-[#6B7280] whitespace-nowrap">
                        {{ $permit->valid_from ? $permit->valid_from->timezone('Asia/Jakarta')->format('d M Y') : '—' }}
                    </td>
                    <td class="px-4 py-2.5 text-[#6B7280] whitespace-nowrap">
                        {{ $permit->valid_until ? $permit->valid_until->timezone('Asia/Jakarta')->format('d M Y') : '—' }}
                    </td>
                    <td class="px-4 py-2.5">
                        <x-status-badge :status="$permit->getStatusEnum()->value" />
                    </td>
                    <td class="px-4 py-2.5 text-[#6B7280] whitespace-nowrap text-xs">
                        {{ $permit->updated_at->timezone('Asia/Jakarta')->format('d M Y H:i') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-4 py-10 text-center text-[#6B7280] text-sm">
                        Tidak ada Work Permit ditemukan.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($permits->hasPages())
    <div class="px-4 py-3 border-t border-[#D1D5DB]">
        {{ $permits->links() }}
    </div>
    @endif
</x-card>

</x-layouts.app>
