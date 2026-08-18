@props(["href" => "#", "active" => false])

<a href="{{ $href }}"
   class="flex items-center gap-2.5 px-4 py-2 text-sm transition
          {{ $active
             ? "text-[#00843D] bg-[#E8F5EE] border-l-4 border-[#00843D] font-semibold pl-3.5"
             : "text-[#6B7280] hover:text-[#333333] hover:bg-[#F3F4F6] border-l-4 border-transparent pl-3.5" }}">
    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
        {{ $icon }}
    </svg>
    {{ $slot }}
</a>
