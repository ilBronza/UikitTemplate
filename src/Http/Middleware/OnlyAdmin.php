<?php

namespace IlBronza\UikitTemplate\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OnlyAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('uikittemplate.onlyAdmin', false) || $this->isExcepted($request)) {
            return $next($request);
        }

        $user = $request->user();

        if ($user && method_exists($user, 'hasAnyRole')
            && $user->hasAnyRole(['administrator', 'superadmin'])) {
            return $next($request);
        }

        $headers = [
            'Cache-Control' => 'no-store, private',
            'Retry-After' => '60',
        ];

        if ($request->expectsJson()) {
            return response()->json([
                'message' => __('uikittemplate::uikittemplate.maintenanceMessage'),
            ], 503, $headers);
        }

        return response()->view('uikittemplate::maintenance', [], 503, $headers);
    }

    protected function isExcepted(Request $request): bool
    {
        foreach (config('uikittemplate.onlyAdminExcept', []) as $pattern) {
            if ($request->is($pattern) || $request->routeIs($pattern)) {
                return true;
            }
        }

        return false;
    }
}
