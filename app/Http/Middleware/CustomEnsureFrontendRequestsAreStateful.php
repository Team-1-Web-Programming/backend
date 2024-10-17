<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

class CustomEnsureFrontendRequestsAreStateful extends EnsureFrontendRequestsAreStateful
{
    protected function configureSecureCookieSessions()
    {
        config([
            'session.domain' => env('SESSION_DOMAIN', 'api-gunaulang.fly.dev'),
            'session.secure' => true,
            'session.http_only' => true,
            'session.same_site' => 'none',
        ]);
    }
}
