@props(["variant" => "primary", "type" => "button", "href" => null])

@php
$variants = [
    "primary"   => "bg-[#00843D] hover:bg-[#006B32] text-white",
    "secondary" => "bg-white hover:bg-[#F3F4F6] text-[#333333] border border-[#D1D5DB]",
    "danger"    => "bg-[#DC3545] hover:bg-[#B02030] text-white",
    "warning"   => "bg-[#F59E0B] hover:bg-[#D97706] text-white",
];
$cls = "inline-flex items-center gap-1.5 px-3 py-1.5 rounded text-sm font-medium transition focus:outline-none focus:ring-2 focus:ring-offset-1 " . ($variants[$variant] ?? $variants["primary"]);
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(["class" => $cls]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(["class" => $cls]) }}>{{ $slot }}</button>
@endif
