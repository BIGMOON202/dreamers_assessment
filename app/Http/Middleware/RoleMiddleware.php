<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  mixed ...$roles
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        if (!in_array($user->role->name, $roles)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return $next($request);
    }
}
