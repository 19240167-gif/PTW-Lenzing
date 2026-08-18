<x-layouts.app>
<x-slot:title>Edit {{ $permit->permit_number }} — PTW Portal</x-slot:title>

<div class="max-w-2xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('permits.show', $permit) }}" class="text-[#6B7280] hover:text-[#333333]">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-xl font-semibold text-[#333333]">Edit Work Permit</h1>
            <p class="text-sm text-[#6B7280] font-mono">{{ $permit->permit_number }}</p>
        </div>
    </div>

    <x-card>
        <form method="POST" action="{{ route('permits.update', $permit) }}">
            @csrf @method('PUT')

            <div class="divide-y divide-[#D1D5DB]">

                <div class="px-5 py-4 space-y-4">
                    <x-section-header title="Informasi Pekerjaan" />

                    <div class="mt-4">
                        <label class="block text-sm font-medium text-[#333333] mb-1">Judul Pekerjaan <span class="text-[#DC3545]">*</span></label>
                        <input type="text" name="title" value="{{ old('title', $permit->title) }}"
                               class="w-full border border-[#D1D5DB] rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#00843D] focus:border-[#00843D]" required>
                        @error('title')<p class="mt-1 text-xs text-[#DC3545]">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-[#333333] mb-1">Deskripsi</label>
                        <textarea name="description" rows="3"
                                  class="w-full border border-[#D1D5DB] rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#00843D]">{{ old('description', $permit->description) }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-[#333333] mb-1">Jenis Permit</label>
                        <select name="permit_type_id" class="w-full border border-[#D1D5DB] rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#00843D]">
                            <option value="">— Pilih —</option>
                            @foreach($permitTypes as $type)
                                <option value="{{ $type->id }}" @selected(old('permit_type_id', $permit->permit_type_id) == $type->id)>{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="px-5 py-4 space-y-4">
                    <x-section-header title="Lokasi" />
                    <div class="mt-4 grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-[#333333] mb-1">Plant</label>
                            <select name="plant_id" class="w-full border border-[#D1D5DB] rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#00843D]">
                                <option value="">— Pilih —</option>
                                @foreach($plants as $plant)
                                    <option value="{{ $plant->id }}" @selected(old('plant_id', $permit->plant_id) == $plant->id)>{{ $plant->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-[#333333] mb-1">Building</label>
                            <select name="building_id" class="w-full border border-[#D1D5DB] rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#00843D]">
                                <option value="">— Pilih —</option>
                                @foreach($buildings as $building)
                                    <option value="{{ $building->id }}" @selected(old('building_id', $permit->building_id) == $building->id)>{{ $building->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="px-5 py-4 space-y-4">
                    <x-section-header title="Validitas" />
                    <div class="mt-4 grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-[#333333] mb-1">Valid From</label>
                            <input type="datetime-local" name="valid_from"
                                   value="{{ old('valid_from', $permit->valid_from?->format('Y-m-d\TH:i')) }}"
                                   class="w-full border border-[#D1D5DB] rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#00843D]">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-[#333333] mb-1">Valid Until</label>
                            <input type="datetime-local" name="valid_until"
                                   value="{{ old('valid_until', $permit->valid_until?->format('Y-m-d\TH:i')) }}"
                                   class="w-full border border-[#D1D5DB] rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#00843D]">
                        </div>
                    </div>
                </div>

            </div>

            <div class="px-5 py-4 bg-[#F3F4F6] flex justify-end gap-3 rounded-b-[6px]">
                <x-btn href="{{ route('permits.show', $permit) }}" variant="secondary">Batal</x-btn>
                <x-btn type="submit">Simpan Perubahan</x-btn>
            </div>
        </form>
    </x-card>
</div>

</x-layouts.app>
