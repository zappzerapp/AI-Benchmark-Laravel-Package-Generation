<?php

namespace VendorName\RequestShield\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use VendorName\RequestShield\ShieldService;

class ProtectRequest
{
    public function __construct(
        private readonly ShieldService $shield
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->shield->shouldBlock($request)) {
            if ($this->show403View()) {
                return response()->view('errors.403', [], 403);
            }
            
            return response('Forbidden', 403);
        }

        return $next($request);
    }

    private function show403View(): bool
    {
        return function_exists('view') && file_exists(resource_path('views/errors/403.blade.php'));
    }
}