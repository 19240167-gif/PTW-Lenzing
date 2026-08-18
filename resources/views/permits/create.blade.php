<x-layouts.app>
<x-slot:title>Buat Work Permit — PTW Portal</x-slot:title>

<div class="max-w-2xl">
    <x-page-header title="Buat Work Permit Baru" description="Isi informasi pekerjaan yang akan dilakukan." />

    <x-card>
        <form method="POST" action="{{ route('permits.store') }}">
            @csrf

            <div class="divide-y divide-[#D1D5DB]">

                {{-- Informasi Pekerjaan --}}
                <div class="px-5 py-4 space-y-4">
                    <x-section-header title="Informasi Pekerjaan" />

                    <div class="mt-4">
                        <label class="block text-sm font-medium text-[#333333] mb-1">
                            Judul Pekerjaan <span class="text-[#DC3545]">*</span>
                        </label>
                        <input type="text" name="title" value="{{ old('title') }}"
                               class="w-full border border-[#D1D5DB] rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#00843D] focus:border-[#00843D] @error('title') border-[#DC3545] @enderror"
                               placeholder="Contoh: Hot Work — Penggantian Pipa Boiler" required>
                        @error('title')
                            <p class="mt-1 text-xs text-[#DC3545]">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-[#333333] mb-1">Deskripsi Pekerjaan</label>
                        <textarea name="description" rows="3"
                                  class="w-full border border-[#D1D5DB] rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#00843D] focus:border-[#00843D]"
                                  placeholder="Detail pekerjaan yang akan dilakukan...">{{ old('description') }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-[#333333] mb-1">Jenis Permit</label>
                        <select name="permit_type_id"
                                class="w-full border border-[#D1D5DB] rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#00843D]">
                            <option value="">— Pilih Jenis Permit —</option>
                            @foreach($permitTypes as $type)
                                <option value="{{ $type->id }}" @selected(old('permit_type_id') == $type->id)>{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Lokasi --}}
                <div class="px-5 py-4 space-y-4">
                    <x-section-header title="Lokasi" />

                    <div class="mt-4 grid grid-cols-2 gap-4">
                        <div x-data="{ plantId: '{{ old('plant_id') }}' }">
                            <label class="block text-sm font-medium text-[#333333] mb-1">Plant</label>
                            <select name="plant_id" x-model="plantId"
                                    class="w-full border border-[#D1D5DB] rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#00843D]">
                                <option value="">— Pilih Plant —</option>
                                @foreach($plants as $plant)
                                    <option value="{{ $plant->id }}">{{ $plant->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-[#333333] mb-1">Building</label>
                            <select name="building_id"
                                    class="w-full border border-[#D1D5DB] rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#00843D]">
                                <option value="">— Pilih Building —</option>
                                @foreach($buildings as $building)
                                    <option value="{{ $building->id }}" @selected(old('building_id') == $building->id)>
                                        {{ $building->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Validitas --}}
                <div class="px-5 py-4 space-y-4">
                    <x-section-header title="Validitas" />

                    <div class="mt-4 grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-[#333333] mb-1">Valid From</label>
                            <input type="datetime-local" name="valid_from" value="{{ old('valid_from') }}"
                                   class="w-full border border-[#D1D5DB] rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#00843D]">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-[#333333] mb-1">Valid Until</label>
                            <input type="datetime-local" name="valid_until" value="{{ old('valid_until') }}"
                                   class="w-full border border-[#D1D5DB] rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#00843D]">
                        </div>
                    </div>
                </div>

            </div>

            {{-- Actions --}}
            <div class="px-5 py-4 bg-[#F3F4F6] flex justify-end gap-3 rounded-b-[6px]">
                <x-btn href="{{ route('permits.index') }}" variant="secondary">Batal</x-btn>
                <x-btn type="submit">Simpan sebagai Draft</x-btn>
            </div>

        </form>
    </x-card>
</div>

</x-layouts.app>
