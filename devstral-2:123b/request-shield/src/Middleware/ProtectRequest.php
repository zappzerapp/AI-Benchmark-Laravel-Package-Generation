<?php

namespace VendorName\RequestShield\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use VendorName\RequestShield\ShieldService;

class ProtectRequest
{
    public function __construct(
        protected ShieldService $shieldService
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->shieldService->isBlocked($request)) {
            return response()->view('shield::blocked', [], 403);
        }

        return $next($request);
    }
}