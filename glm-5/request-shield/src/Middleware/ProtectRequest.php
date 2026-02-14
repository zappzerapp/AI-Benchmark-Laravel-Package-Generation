<?php

namespace VendorName\RequestShield\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use VendorName\RequestShield\ShieldService;

final readonly class ProtectRequest
{
    public function __construct(
        private ShieldService $shield
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->shield->shouldBlock($request)) {
            $this->shield->logBlockedRequest($request);
            $this->shield->recordBlock();

            return $this->buildBlockedResponse($request);
        }

        return $next($request);
    }

    private function buildBlockedResponse(Request $request): Response
    {
        $config = config('shield.response', []);
        $message = $config['message'] ?? 'Access denied. Your request has been blocked.';

        if ($redirect = $config['redirect'] ?? null) {
            return redirect()->to($redirect);
        }

        if ($view = $config['view'] ?? null) {
            return response()->view($view, ['message' => $message], 403);
        }

        return response()->json([
            'error' => 'Forbidden',
            'message' => $message,
        ], 403);
    }
}