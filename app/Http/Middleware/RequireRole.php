<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

// Purpose: Restricts protected routes by authenticated user role.
class RequireRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        if (count($roles) === 0 || in_array($user->role, $roles, true)) {
            return $next($request);
        }

        abort(403, 'You are not allowed to access this section.');
    }
}
