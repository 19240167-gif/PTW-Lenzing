<?php

namespace App\Auth;

use App\Models\User;

/**
 * Resolves an external identity payload (from SSO, Windows Auth, SAML, OIDC,
 * or development bypass) into an internal PTW User.
 *
 * The PTW application never stores Windows/AD passwords. This interface is the
 * only point where external identity is converted to a PTW User.
 *
 * Resolution priority (implemented by concrete resolvers):
 *   1. UPN match
 *   2. domain + username match
 *   3. email fallback (only if clearly valid as identity)
 *
 * Production SSO implementation: NEEDS CONFIRMATION from IT department.
 * Do not implement a concrete SSO resolver until the mechanism is confirmed.
 */
interface IdentityResolverInterface
{
    /**
     * @param  array{
     *   upn?:      string|null,
     *   domain?:   string|null,
     *   username?: string|null,
     *   email?:    string|null,
     *   name?:     string|null,
     * } $externalIdentity  Normalized identity payload from the auth source.
     * @return User|null  Returns null if no matching active user is found.
     */
    public function resolve(array $externalIdentity): ?User;
}
