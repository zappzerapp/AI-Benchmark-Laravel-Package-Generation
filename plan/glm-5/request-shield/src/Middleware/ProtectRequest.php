<?php

namespace VendorName\RequestShield\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use VendorName\RequestShield\ShieldService;

readonly class ProtectRequest
{
    public function __construct(
        private ShieldService $service
    ) {}

    public function handle(Request $request, Closure $next): SymfonyResponse
    {
        $ip = $request->ip();
        $userAgent = $request->userAgent();

        if ($this->service->shouldBlock($ip, $userAgent)) {
            $this->service->recordBlocked($ip, $userAgent);

            if ($this->service->getResponseMode() === 'view') {
                return response()
                    ->view($this->service->getBlockedView(), [], 403);
            }

            abort(403, 'Access Denied');
        }

        return $next($request);
    }
}