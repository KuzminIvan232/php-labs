<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Lab 5: role-based access control. Registered as the 'role' middleware
 * alias (see bootstrap/app.php). Usage: ->middleware('role:manager') —
 * passes for 'manager' AND 'admin' (client < manager < admin hierarchy,
 * see User::hasAtLeastRole()), mirroring Symfony's role_hierarchy.
 *
 * Runs after 'auth:api', so $request->user() is always the fresh,
 * DB-backed account (never a stale role baked into an old token).
 */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        /** @var User|null $user */
        $user = $request->user();

        if (!$user || !$user->hasAtLeastRole($role)) {
            return response()->json(['data' => ['error' => 'Forbidden: requires role ' . $role . ' or higher']], 403);
        }

        return $next($request);
    }
}
