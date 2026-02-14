<?php

declare(strict_types=1);

namespace VendorName\RequestShield\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use VendorName\RequestShield\ShieldService;

final readonly class ProtectRequest
{
    public function __construct(
        private ShieldService $shield,
    ) {
    }

    /**
     * Handle an incoming request
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->shield->shouldBlock($request)) {
            $reason = $this->getBlockReason($request);
            $this->shield->logBlockedRequest($request, $reason);

            return $this->blockRequest($request, $reason);
        }

        return $next($request);
    }

    /**
     * Determine the block reason
     */
    private function getBlockReason(Request $request): string
    {
        if ($this->shield->isIpBlocked($request->ip())) {
            return 'Blocked IP: ' . $request->ip();
        }

        if ($this->shield->isUserAgentBlocked($request->userAgent())) {
            return 'Blocked User-Agent: ' . $request->userAgent();
        }

        return 'Unknown reason';
    }

    /**
     * Block the request based on configuration
     */
    private function blockRequest(Request $request, string $reason): Response
    {
        $responseType = config('shield.response_type', 'exception');

        return match ($responseType) {
            'view' => $this->viewResponse($reason),
            'json' => $this->jsonResponse($reason),
            default => throw new HttpException(403, 'Access Forbidden: ' . $reason),
        };
    }

    /**
     * Return a view response
     */
    private function viewResponse(string $reason): Response
    {
        $view = config('shield.blocked_view', 'shield::blocked');

        return response()->view($view, [
            'reason' => $reason,
            'timestamp' => now()->toDateTimeString(),
        ], 403);
    }

    /**
     * Return a JSON response
     */
    private function jsonResponse(string $reason): Response
    {
        return response()->json([
            'message' => 'Access Forbidden',
            'reason' => $reason,
            'timestamp' => now()->toIso8601String(),
        ], 403);
    }
}
