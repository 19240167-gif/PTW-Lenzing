<?php

namespace App\Enums;

enum WorkPermitStatus: string
{
    case DRAFT                = "draft";
    case SUBMITTED            = "submitted";
    case APPROVAL             = "approval";
    case APPROVED             = "approved";
    case RELEASE              = "release";
    case ACTIVE               = "active";
    case FINISH_NOTIFICATION  = "finish_notification";
    case CLOSED               = "closed";
    case REJECTED             = "rejected";
    case REVISION             = "revision";

    /**
     * Valid transitions from this status.
     * Only transitions explicitly listed here are allowed.
     */
    public function allowedTransitions(): array
    {
        return match($this) {
            self::DRAFT               => [self::SUBMITTED],
            self::SUBMITTED           => [self::APPROVAL],
            self::APPROVAL            => [self::APPROVED, self::REJECTED],
            self::APPROVED            => [self::RELEASE],
            self::REJECTED            => [self::REVISION],
            self::REVISION            => [self::SUBMITTED],
            self::RELEASE             => [self::ACTIVE],
            self::ACTIVE              => [self::FINISH_NOTIFICATION],
            self::FINISH_NOTIFICATION => [self::CLOSED],
            self::CLOSED              => [],
        };
    }

    public function canTransitionTo(self $next): bool
    {
        return in_array($next, $this->allowedTransitions(), strict: true);
    }

    public function label(): string
    {
        return match($this) {
            self::DRAFT               => "Draft",
            self::SUBMITTED           => "Submitted",
            self::APPROVAL            => "Approval",
            self::APPROVED            => "Approved",
            self::RELEASE             => "Release",
            self::ACTIVE              => "Active",
            self::FINISH_NOTIFICATION => "Finish Notification",
            self::CLOSED              => "Closed",
            self::REJECTED            => "Rejected",
            self::REVISION            => "Revision",
        };
    }

    /** Returns true for terminal states that cannot be edited */
    public function isTerminal(): bool
    {
        return in_array($this, [self::CLOSED], strict: true);
    }

    /** Returns true for statuses where the PTW body can still be edited */
    public function isEditable(): bool
    {
        return in_array($this, [self::DRAFT, self::REVISION], strict: true);
    }
}
