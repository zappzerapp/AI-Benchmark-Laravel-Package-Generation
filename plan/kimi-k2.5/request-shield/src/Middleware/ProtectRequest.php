<?php

declare(strict_types=1);

namespace VendorName\RequestShield\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use VendorName\RequestShield\ShieldService;

final readonly class ProtectRequest
{
    public function __construct(
        private ShieldService $shield,
    ) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip() ?? '';
        $userAgent = $request->userAgent() ?? '';

        if ($this->shield->shouldBlock($ip, $userAgent)) {
            $this->shield->recordBlocked();

            $mode = config('shield.response_mode', 'abort');

            if ($mode === 'view') {
                $view = config('shield.blocked_view', 'request-shield::blocked');

                return response()->view($view, [], Response::HTTP_FORBIDDEN);
            }

            abort(Response::HTTP_FORBIDDEN, 'Access denied.');
        }

        return $next($request);
    }
}
