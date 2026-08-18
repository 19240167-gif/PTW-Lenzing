<?php

namespace App\Enums;

enum AssignmentType: string
{
    case REQUESTER = "requester";
    case APPROVER  = "approver";
    case RELEASE   = "release";

    // Additional types are NEEDS CONFIRMATION from company requirements.
    // Do not add reviewer / hse_officer / finish_report / area_owner
    // until confirmed by the company.

    public function label(): string
    {
        return match($this) {
            self::REQUESTER => "Requester",
            self::APPROVER  => "Approver",
            self::RELEASE   => "Released By",
        };
    }
}
