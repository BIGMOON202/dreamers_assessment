<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class ImpersonateUser
{
    public function handle(Request $request, Closure $next)
    {
        // Simple header-based user impersonation so APIs can be
        // exercised easily from tools like Postman in non-production
        // environments.
        //
        // Header: X-User-Id: {user_id}
        if (App::environment(['local', 'testing'])) {
            $userId = $request->header('X-User-Id');

            if ($userId && ! Auth::check()) {
                $user = User::find($userId);

                if ($user) {
                    Auth::login($user);
                }
            }
        }

        return $next($request);
    }
}


