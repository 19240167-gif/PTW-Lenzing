<?php

namespace App\Enums;

enum ApprovalStatus: string
{
    case PENDING  = "pending";
    case APPROVED = "approved";
    case REJECTED = "rejected";
    case SKIPPED  = "skipped";

    public function label(): string
    {
        return match($this) {
            self::PENDING  => "Pending",
            self::APPROVED => "Approved",
            self::REJECTED => "Rejected",
            self::SKIPPED  => "Skipped",
        };
    }

    public function isActed(): bool
    {
        return $this !== self::PENDING;
    }
}
