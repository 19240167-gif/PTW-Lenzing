<?php

namespace App\Auth;

/**
 * Normalizes raw identity data from any external source into a consistent format.
 *
 * Handles formats like:
 *   "LAGGRF\spvtrain1"  → domain=LAGGRF, username=spvtrain1
 *   "spvtraining1@pt-spv.com" → upn=spvtraining1@pt-spv.com
 */
class IdentityNormalizer
{
    /**
     * @param  array $raw  Raw payload from SSO/Windows/dev source.
     * @return array{upn: string|null, domain: string|null, username: string|null, email: string|null, name: string|null}
     */
    public static function normalize(array $raw): array
    {
        $domain   = null;
        $username = null;
        $upn      = null;
        $email    = null;
        $name     = $raw["name"] ?? null;

        // Parse "DOMAIN\username" format
        $samAccount = $raw["sAMAccountName"] ?? $raw["windows_identity"] ?? null;
        if ($samAccount && str_contains($samAccount, "\\")) {
            [$domain, $username] = explode("\\", $samAccount, 2);
            $domain   = strtoupper(trim($domain));
            $username = strtolower(trim($username));
        }

        // Direct domain/username fields
        if (!$domain && isset($raw["domain"])) {
            $domain = strtoupper(trim($raw["domain"]));
        }
        if (!$username && isset($raw["username"])) {
            $username = strtolower(trim($raw["username"]));
        }

        // UPN — e.g. user@pt-spv.com
        if (isset($raw["upn"])) {
            $upn = strtolower(trim($raw["upn"]));
        } elseif (isset($raw["userPrincipalName"])) {
            $upn = strtolower(trim($raw["userPrincipalName"]));
        }

        // Email (may differ from UPN)
        if (isset($raw["email"])) {
            $email = strtolower(trim($raw["email"]));
        } elseif (isset($raw["mail"])) {
            $email = strtolower(trim($raw["mail"]));
        }

        return compact("upn", "domain", "username", "email", "name");
    }
}
