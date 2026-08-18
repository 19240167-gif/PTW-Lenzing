<?php

namespace App\Auth;

use App\Models\User;
use RuntimeException;

/**
 * Development-only identity resolver.
 *
 * Resolves identity by matching user_id from a trusted development dropdown.
 * NEVER enabled in production.
 *
 * Guard: constructor throws if APP_ENV=production or AUTH_MODE != development.
 */
class DevelopmentIdentityResolver implements IdentityResolverInterface
{
    public function __construct()
    {
        if (app()->environment("production")) {
            throw new RuntimeException(
                "DevelopmentIdentityResolver must not be used in production."
            );
        }

        if (config("auth.mode") !== "development") {
            throw new RuntimeException(
                "DevelopmentIdentityResolver requires AUTH_MODE=development."
            );
        }
    }

    /**
     * Resolves by user_id (passed directly from the dev login dropdown).
     * No password involved.
     */
    public function resolve(array $externalIdentity): ?User
    {
        $userId = $externalIdentity["user_id"] ?? null;

        if (!$userId) {
            return null;
        }

        return User::where("id", $userId)
                   ->where("is_active", true)
                   ->first();
    }
}
