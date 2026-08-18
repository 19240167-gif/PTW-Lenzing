<x-layouts.app>
<x-slot:title>{{ $permit->permit_number }} — PTW Portal</x-slot:title>

{{-- Flash messages --}}
@if(session('success'))
    <div class="mb-4 flex items-center gap-2 bg-[#E8F5EE] border border-[#00843D] text-[#006B32] rounded px-3 py-2 text-sm">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div class="mb-4 flex items-center gap-2 bg-red-50 border border-red-200 text-[#DC3545] rounded px-3 py-2 text-sm">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01"/></svg>
        {{ session('error') }}
    </div>
@endif

{{-- Header bar --}}
<div class="flex items-start justify-between mb-5">
    <div>
        <div class="flex items-center gap-3">
            <h1 class="text-xl font-semibold text-[#333333] font-mono">{{ $permit->permit_number }}</h1>
            <x-status-badge :status="$permit->getStatusEnum()->value" />
        </div>
        <p class="text-[#6B7280] text-sm mt-1">{{ $permit->title }}</p>
    </div>
    <div class="flex items-center gap-2 flex-wrap justify-end">
        @if($canEdit)
            <x-btn href="{{ route('permits.edit', $permit) }}" variant="secondary">Edit</x-btn>
        @endif

        @if($canSubmit)
            <form method="POST" action="{{ route('permits.submit', $permit) }}">
                @csrf
                <x-btn type="submit">Submit</x-btn>
            </form>
        @endif

        @if(auth()->user()->is_global_admin && $permit->getStatusEnum() === \App\Enums\WorkPermitStatus::SUBMITTED)
            <form method="POST" action="{{ route('permits.send-to-approval', $permit) }}">
                @csrf
                <x-btn type="submit" variant="primary">Kirim ke Approval</x-btn>
            </form>
        @endif

        @if($canRelease)
            <form method="POST" action="{{ route('permits.release', $permit) }}">
                @csrf
                <x-btn type="submit">Release</x-btn>
            </form>
        @endif

        @if($canActivate)
            <form method="POST" action="{{ route('permits.activate', $permit) }}">
                @csrf
                <x-btn type="submit">Aktifkan</x-btn>
            </form>
        @endif
    </div>
</div>

<div class="grid grid-cols-3 gap-4">

    {{-- Main content (2/3) --}}
    <div class="col-span-2 space-y-4">

        {{-- Informasi Pekerjaan --}}
        <x-card>
            <x-section-header title="Informasi Pekerjaan" />
            <div class="px-4 py-3 grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
                <div>
                    <p class="text-xs text-[#6B7280]">Requester</p>
                    <p class="font-medium">{{ $permit->requester?->name ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-[#6B7280]">Jenis Permit</p>
                    <p class="font-medium">{{ $permit->permitType?->name ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-[#6B7280]">Plant</p>
                    <p class="font-medium">{{ $permit->plant?->name ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-[#6B7280]">Building</p>
                    <p class="font-medium">{{ $permit->building?->name ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-[#6B7280]">Valid From</p>
                    <p class="font-medium">{{ $permit->valid_from?->timezone('Asia/Jakarta')->format('d M Y H:i') ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-[#6B7280]">Valid Until</p>
                    <p class="font-medium">{{ $permit->valid_until?->timezone('Asia/Jakarta')->format('d M Y H:i') ?? '—' }}</p>
                </div>
                @if($permit->description)
                <div class="col-span-2">
                    <p class="text-xs text-[#6B7280]">Deskripsi</p>
                    <p class="font-medium whitespace-pre-line">{{ $permit->description }}</p>
                </div>
                @endif
            </div>
        </x-card>

        {{-- Approval --}}
        <x-card>
            <x-section-header title="Approval" />
            <div class="divide-y divide-[#D1D5DB]">
                @php $currentCycle = $permit->currentCycleNumber(); $currentApprovals = $permit->approvals->where("cycle_number", $currentCycle)->sortBy("approval_order"); @endphp
                @forelse($currentApprovals as $approval)
                <div class="px-4 py-3 flex items-center gap-3">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-[#333333]">
                            Approver {{ $approval->approval_order }} — {{ $approval->approver?->name ?? '—' }}
                        </p>
                        @if($approval->comment)
                            <p class="text-xs text-[#6B7280] mt-0.5 italic">"{{ $approval->comment }}"</p>
                        @endif
                        @if($approval->approved_at || $approval->rejected_at)
                            <p class="text-xs text-[#6B7280] mt-0.5">
                                {{ ($approval->approved_at ?? $approval->rejected_at)?->timezone('Asia/Jakarta')->format('d M Y H:i') }}
                            </p>
                        @endif
                    </div>
                    <x-status-badge :status="$approval->status->value" />
                </div>
                @empty
                <div class="px-4 py-4 text-sm text-[#6B7280]">Belum ada approver yang ditugaskan.</div>
                @endforelse
            </div>

            {{-- Approve/Reject actions --}}
            @if($canApprove || $canReject)
            <div class="px-4 py-3 border-t border-[#D1D5DB] bg-[#F3F4F6] rounded-b-[6px]" x-data="{ action: '' }">
                <p class="text-xs font-semibold text-[#6B7280] uppercase tracking-wide mb-3">Tindakan Approval</p>
                <form method="POST" :action="action === 'approve' ? '{{ route('permits.approve', $permit) }}' : '{{ route('permits.reject', $permit) }}'">
                    @csrf
                    <textarea name="comment" rows="2" placeholder="Komentar (wajib jika Reject)..."
                              class="w-full border border-[#D1D5DB] rounded px-3 py-2 text-sm mb-3 focus:outline-none focus:ring-2 focus:ring-[#00843D]"></textarea>
                    <div class="flex gap-2">
                        @if($canApprove)
                        <x-btn type="submit" @click="action='approve'">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            Approve
                        </x-btn>
                        @endif
                        @if($canReject)
                        <x-btn type="submit" variant="danger" @click="action='reject'">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            Reject
                        </x-btn>
                        @endif
                    </div>
                </form>
            </div>
            @endif

            {{-- Finish / Close actions --}}
            @if($canFinish)
            <div class="px-4 py-3 border-t border-[#D1D5DB]">
                <form method="POST" action="{{ route('permits.finish', $permit) }}">
                    @csrf
                    <textarea name="comment" rows="2" placeholder="Laporan penyelesaian pekerjaan..."
                              class="w-full border border-[#D1D5DB] rounded px-3 py-2 text-sm mb-2 focus:outline-none focus:ring-2 focus:ring-[#00843D]"></textarea>
                    <x-btn type="submit" variant="warning">Finish Notification</x-btn>
                </form>
            </div>
            @endif

            @if($canClose)
            <div class="px-4 py-3 border-t border-[#D1D5DB]">
                <form method="POST" action="{{ route('permits.close', $permit) }}">
                    @csrf
                    <x-btn type="submit" variant="secondary">Tutup PTW</x-btn>
                </form>
            </div>
            @endif
        </x-card>

        {{-- Attachments --}}
        <x-card>
            <x-section-header title="Attachment" />
            <div class="divide-y divide-[#D1D5DB]">
                @forelse($permit->attachments as $att)
                <div class="px-4 py-2.5 flex items-center gap-3">
                    <svg class="w-4 h-4 text-[#6B7280] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                    </svg>
                    <div class="flex-1 min-w-0">
                        <a href="{{ route('attachments.download', $att) }}"
                           class="text-sm font-medium text-[#00843D] hover:underline truncate block">{{ $att->file_name }}</a>
                        <p class="text-xs text-[#6B7280]">
                            {{ $att->uploadedBy?->name ?? '—' }} &middot;
                            {{ $att->created_at->timezone('Asia/Jakarta')->format('d M Y H:i') }} &middot;
                            {{ $att->formattedSize() }}
                        </p>
                    </div>
                    @if(auth()->user()->can('edit', $permit))
                    <form method="POST" action="{{ route('attachments.destroy', $att) }}"
                          onsubmit="return confirm('Hapus attachment ini?')">
                        @csrf @method('DELETE')
                        <button class="text-[#6B7280] hover:text-[#DC3545] transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </form>
                    @endif
                </div>
                @empty
                <div class="px-4 py-4 text-sm text-[#6B7280]">Belum ada attachment.</div>
                @endforelse
            </div>
            <div class="px-4 py-3 border-t border-[#D1D5DB]">
                <form method="POST" action="{{ route('attachments.store', $permit) }}" enctype="multipart/form-data" class="flex items-center gap-3">
                    @csrf
                    <input type="file" name="file" class="text-sm text-[#6B7280] file:mr-2 file:py-1 file:px-3 file:rounded file:border file:border-[#D1D5DB] file:text-sm file:bg-white file:text-[#333333] hover:file:bg-[#F3F4F6]">
                    <x-btn type="submit" variant="secondary">Upload</x-btn>
                </form>
            </div>
        </x-card>

        {{-- Status History --}}
        <x-card>
            <x-section-header title="Status History" />
            <div class="divide-y divide-[#D1D5DB]">
                @forelse($permit->statusHistories as $history)
                <div class="px-4 py-2.5 flex items-start gap-3">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 text-sm">
                            @if($history->from_status)
                                <x-status-badge :status="$history->from_status" />
                                <svg class="w-3 h-3 text-[#6B7280]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                            @endif
                            <x-status-badge :status="$history->to_status" />
                        </div>
                        @if($history->comment)
                            <p class="text-xs text-[#6B7280] mt-1 italic">"{{ $history->comment }}"</p>
                        @endif
                    </div>
                    <div class="text-right shrink-0">
                        <p class="text-xs text-[#333333]">{{ $history->changedBy?->name ?? 'System' }}</p>
                        <p class="text-xs text-[#6B7280]">{{ $history->created_at->timezone('Asia/Jakarta')->format('d M Y H:i') }}</p>
                    </div>
                </div>
                @empty
                <div class="px-4 py-4 text-sm text-[#6B7280]">Belum ada history.</div>
                @endforelse
            </div>
        </x-card>

    </div>

    {{-- Sidebar (1/3) --}}
    <div class="space-y-4">

        {{-- Assignment --}}
        <x-card>
            <x-section-header title="Assignment" />
            <div class="divide-y divide-[#D1D5DB]">
                @forelse($permit->activeAssignments as $assignment)
                <div class="px-4 py-2.5 flex items-center gap-2">
                    <div class="flex-1 min-w-0">
                        <p class="text-xs text-[#6B7280]">{{ $assignment->assignment_type->label() }}
                            @if($assignment->approval_order) #{{ $assignment->approval_order }} @endif
                        </p>
                        <p class="text-sm font-medium text-[#333333] truncate">{{ $assignment->user?->name ?? '—' }}</p>
                        <p class="text-xs text-[#6B7280]">{{ $assignment->user?->position ?? '' }}</p>
                    </div>
                    @if(auth()->user()->is_global_admin || auth()->user()->can('edit', $permit))
                    <form method="POST" action="{{ route('permits.unassign', [$permit, $assignment]) }}"
                          onsubmit="return confirm('Hapus penugasan ini?')">
                        @csrf @method('DELETE')
                        <button class="text-[#6B7280] hover:text-[#DC3545]">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </form>
                    @endif
                </div>
                @empty
                <div class="px-4 py-4 text-sm text-[#6B7280]">Belum ada assignment.</div>
                @endforelse
            </div>

            @if(auth()->user()->is_global_admin || auth()->user()->can('edit', $permit))
            <div class="px-4 py-3 border-t border-[#D1D5DB] bg-[#F3F4F6] rounded-b-[6px]" x-data="{ type: 'approver' }">
                <p class="text-xs font-semibold text-[#6B7280] uppercase tracking-wide mb-2">Tambah Assignment</p>
                <form method="POST" action="{{ route('permits.assign', $permit) }}" class="space-y-2">
                    @csrf
                    <select name="assignment_type" x-model="type"
                            class="w-full border border-[#D1D5DB] rounded px-2.5 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-[#00843D]">
                        <option value="approver">Approver</option>
                        <option value="release">Released By</option>
                    </select>
                    <template x-if="type === 'approver'">
                        <input type="number" name="approval_order" placeholder="Urutan (1, 2, ...)" min="1"
                               class="w-full border border-[#D1D5DB] rounded px-2.5 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-[#00843D]">
                    </template>
                    <select name="user_id"
                            class="w-full border border-[#D1D5DB] rounded px-2.5 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-[#00843D]">
                        <option value="">— Pilih User —</option>
                        @foreach($availableUsers as $u)
                            <option value="{{ $u->id }}">{{ $u->name }} {{ $u->username ? '('.$u->username.')' : '' }}</option>
                        @endforeach
                    </select>
                    <x-btn type="submit" class="w-full justify-center">Tugaskan</x-btn>
                </form>
            </div>
            @endif
        </x-card>

        {{-- Revise action for rejected permits --}}
        @if($permit->getStatusEnum() === \App\Enums\WorkPermitStatus::REJECTED && auth()->user()->can('submit', $permit))
        <x-card class="border-amber-200">
            <div class="px-4 py-4">
                <p class="text-sm font-semibold text-amber-700 mb-2">Work Permit Ditolak</p>
                <p class="text-xs text-[#6B7280] mb-3">Perbaiki dan ajukan kembali untuk memulai siklus approval baru.</p>
                <form method="POST" action="{{ route('permits.revise', $permit) }}">
                    @csrf
                    <x-btn type="submit" variant="warning" class="w-full justify-center">Mulai Revisi</x-btn>
                </form>
            </div>
        </x-card>
        @endif

    </div>

</div>

</x-layouts.app>


