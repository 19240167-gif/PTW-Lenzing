<x-layouts.guest>
    <x-slot:title>PTW Portal — Development Login</x-slot:title>

    <div class="w-full max-w-sm">
        {{-- Header --}}
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-[#00843D] mb-4">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <h1 class="text-xl font-bold text-[#333333]">PTW Portal</h1>
            <p class="text-sm text-[#6B7280] mt-1">Permit to Work Management</p>
        </div>

        {{-- Dev Warning Banner --}}
        <div class="mb-4 flex items-center gap-2 bg-amber-50 border border-amber-200 rounded px-3 py-2 text-amber-700 text-xs font-medium">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
            </svg>
            Development Mode — No password required
        </div>

        {{-- Login Card --}}
        <x-card class="px-6 py-6">
            <form method="POST" action="{{ route("auth.dev-login.post") }}">
                @csrf

                <div class="mb-5">
                    <label for="user_id" class="block text-sm font-medium text-[#333333] mb-1.5">
                        Login sebagai
                    </label>
                    <select name="user_id" id="user_id"
                            class="w-full border border-[#D1D5DB] rounded px-3 py-2 text-sm text-[#333333]
                                   focus:outline-none focus:ring-2 focus:ring-[#00843D] focus:border-[#00843D]"
                            required>
                        <option value="">— Pilih user —</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">
                                {{ $user->name }}
                                @if($user->username) ({{ $user->username }}) @endif
                                @if($user->is_global_admin) [Admin] @endif
                            </option>
                        @endforeach
                    </select>
                    @error("user_id")
                        <p class="mt-1 text-xs text-[#DC3545]">{{ $message }}</p>
                    @enderror
                </div>

                <x-btn type="submit" class="w-full justify-center">
                    Masuk
                </x-btn>
            </form>
        </x-card>

        <p class="text-center mt-4 text-xs text-[#6B7280]">
            {{ config("app.name") }} &mdash; Internal Use Only
        </p>
    </div>
</x-layouts.guest>
