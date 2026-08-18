@props(["status"])

@php
$map = [
    // WorkPermit statuses
    "draft"                => ["bg-[#F3F4F6] text-[#6B7280] ring-1 ring-[#D1D5DB]",        "Draft"],
    "submitted"            => ["bg-blue-50 text-blue-700 ring-1 ring-blue-200",             "Submitted"],
    "approval"             => ["bg-amber-50 text-amber-700 ring-1 ring-amber-200",           "Approval"],
    "approved"             => ["bg-[#E8F5EE] text-[#00843D] ring-1 ring-[#00843D]",         "Approved"],
    "release"              => ["bg-[#E8F5EE] text-[#006B32] ring-1 ring-[#006B32]",         "Release"],
    "active"               => ["bg-[#00843D] text-white",                                   "Active"],
    "finish_notification"  => ["bg-amber-50 text-amber-700 ring-1 ring-amber-200",           "Finish Notif."],
    "closed"               => ["bg-[#F3F4F6] text-[#6B7280] ring-1 ring-[#D1D5DB]",        "Closed"],
    "rejected"             => ["bg-red-50 text-[#DC3545] ring-1 ring-red-200",              "Rejected"],
    "revision"             => ["bg-amber-50 text-amber-700 ring-1 ring-amber-200",           "Revision"],
    // ApprovalStatus
    "pending"              => ["bg-amber-50 text-amber-700 ring-1 ring-amber-200",           "Pending"],
    "skipped"              => ["bg-[#F3F4F6] text-[#6B7280] ring-1 ring-[#D1D5DB]",        "Skipped"],
];

$classes = $map[$status] ?? ["bg-gray-100 text-gray-600 ring-1 ring-gray-200", ucfirst($status)];
@endphp

<span class="inline-flex items-center rounded px-2 py-0.5 text-xs font-semibold {{ $classes[0] }}">
    {{ $classes[1] }}
</span>
