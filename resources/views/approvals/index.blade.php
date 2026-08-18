<x-layouts.app>
<x-slot:title>My Approvals — PTW Portal</x-slot:title>

<x-page-header title="My Approvals" description="Work Permit yang menunggu persetujuan Anda." />

<x-card>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-[#F3F4F6] border-b border-[#D1D5DB] text-left text-xs font-semibold text-[#6B7280] uppercase tracking-wide">
                    <th class="px-4 py-2.5">Permit</th>
                    <th class="px-4 py-2.5">Plant</th>
                    <th class="px-4 py-2.5">Building</th>
                    <th class="px-4 py-2.5">Urutan</th>
                    <th class="px-4 py-2.5">Valid Until</th>
                    <th class="px-4 py-2.5">Status PTW</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#D1D5DB]">
                @forelse($approvals as $approval)
                <tr class="hover:bg-[#E8F5EE] transition cursor-pointer"
                    onclick="window.location='{{ route('permits.show', $approval->workPermit) }}'">
                    <td class="px-4 py-2.5">
                        <a href="{{ route('permits.show', $approval->workPermit) }}"
                           class="font-mono font-semibold text-[#00843D] hover:underline text-xs">
                            {{ $approval->workPermit->permit_number }}
                        </a>
                        <p class="text-[#333333] text-sm mt-0.5 truncate max-w-[200px]">
                            {{ $approval->workPermit->title }}
                        </p>
                    </td>
                    <td class="px-4 py-2.5 text-[#333333]">{{ $approval->workPermit->plant?->name ?? '—' }}</td>
                    <td class="px-4 py-2.5 text-[#333333]">{{ $approval->workPermit->building?->name ?? '—' }}</td>
                    <td class="px-4 py-2.5 text-[#6B7280]">Approver #{{ $approval->approval_order }}</td>
                    <td class="px-4 py-2.5 text-[#6B7280] whitespace-nowrap">
                        {{ $approval->workPermit->valid_until?->timezone('Asia/Jakarta')->format('d M Y') ?? '—' }}
                    </td>
                    <td class="px-4 py-2.5">
                        <x-status-badge :status="$approval->workPermit->getStatusEnum()->value" />
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-10 text-center text-[#6B7280] text-sm">
                        Tidak ada approval yang menunggu.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($approvals->hasPages())
        <div class="px-4 py-3 border-t border-[#D1D5DB]">{{ $approvals->links() }}</div>
    @endif
</x-card>

</x-layouts.app>
