<?php

namespace VendorName\RequestShield\Middleware;

use Closure;
use VendorName\RequestShield\Facades\Shield;
use VendorName\RequestShield\ShieldService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ProtectRequest
{
    public function __construct(private readonly ShieldService $shieldService)
    {
    }

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if the IP is blocked
        if ($this->shieldService->isIpBlocked($request->ip())) {
            return $this->respondBlocked();
        }

        // Check if the user agent is blocked
        if ($this->shieldService->isUserAgentBlocked($request->userAgent())) {
            return $this->respondBlocked();
        }

        return $next($request);
    }

    /**
     * Respond to a blocked request
     *
     * @return Response
     */
    private function respondBlocked(): Response
    {
        $responseType = $this->shieldService->getResponseType();

        if ($responseType === 'view') {
            // Return a 403 view
            return response(view('shield-403'), 403);
        }

        // Default to throwing an exception
        throw new HttpException(403, 'Forbidden');
    }
}