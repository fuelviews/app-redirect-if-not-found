<?php

namespace Fuelviews\AppRedirectIfNotFound\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AppRedirectIfNotFoundMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->shouldApplyMiddleware() || $this->isExcludedRoute($request)) {
            return $next($request);
        }

        $response = $next($request);

        if (in_array($response->status(), [404, 500], true)) {
            return redirect()->route(config('app-redirect-if-not-found.fallback_route', 'home'))
                ->setStatusCode('301');
        }

        return $response;
    }

    /**
     * Determine if middleware should be applied based on the environment.
     */
    protected function shouldApplyMiddleware(): bool
    {
        return app()->environment('production', 'development');
    }

    /**
     * Check if the request matches any excluded route patterns.
     */
    protected function isExcludedRoute(Request $request): bool
    {
        $excludedPatterns = [
            'dashboard/admin/*',
            'livewire/*',
            'api/*',
            'glide/*',
        ];

        foreach ($excludedPatterns as $pattern) {
            if ($request->is($pattern)) {
                return true;
            }
        }

        return false;
    }
}
