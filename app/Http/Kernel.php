<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    // Global middleware
    protected $middleware = [
        // You can leave this empty for API-only
    ];

    // Middleware groups
    protected $middlewareGroups = [
        'web' => [],
        'api' => [
            'throttle:api',
        ],
    ];

    // Route middleware
    protected $routeMiddleware = [
        'role' => \App\Http\Middleware\RoleMiddleware::class,
        // add auth if you later use Sanctum or token-based auth
        //'auth' => \App\Http\Middleware\Authenticate::class,
    ];
}
