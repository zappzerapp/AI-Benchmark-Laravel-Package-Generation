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
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->shield->isBlocked($request)) {
            $this->shield->recordBlocked($request);

            $view = config('shield.forbidden_view');

            if ($view && view()->exists($view)) {
                return response()->view($view, [], Response::HTTP_FORBIDDEN);
            }

            throw new HttpException(Response::HTTP_FORBIDDEN, 'Forbidden');
        }

        return $next($request);
    }
}
