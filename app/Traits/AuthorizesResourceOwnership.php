<?php

namespace App\Traits;

use Throwable;

trait AuthorizesResourceOwnership
{
    /**
     * Allow the request through when the authenticated user owns the
     * resource (matches $ownerId) or holds one of the given roles;
     * otherwise throw the given exception.
     */
    protected function authorizeOwnerOrRoles(int $ownerId, array $allowedRoles, Throwable $exception): void
    {
        $user = auth()->user();

        if ($user->id === $ownerId || $user->hasAnyRole($allowedRoles)) {
            return;
        }

        throw $exception;
    }
}
