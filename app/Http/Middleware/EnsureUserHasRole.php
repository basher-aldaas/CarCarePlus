<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Role gate for API (Sanctum) routes.
 *
 * Uses the authenticated request user (resolved by auth:sanctum) and checks
 * roles against the model's default guard (web) — avoiding the guard mismatch
 * that spatie's own middleware hits when tokens authenticate on the sanctum guard.
 *
 * Usage: ->middleware('role:super_admin') or 'role:admin,super_admin'
 */
class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! $user->hasAnyRole($roles)) {
            throw new AccessDeniedHttpException();
        }

        return $next($request);
    }
}
