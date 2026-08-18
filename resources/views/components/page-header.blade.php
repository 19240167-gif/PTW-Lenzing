@props(["title", "description" => null])

<div class="mb-6">
    <h1 class="text-xl font-semibold text-[#333333]">{{ $title }}</h1>
    @if($description)
        <p class="mt-1 text-sm text-[#6B7280]">{{ $description }}</p>
    @endif
    @isset($actions)
        <div class="mt-3 flex gap-2">{{ $actions }}</div>
    @endisset
</div>
