<?php

declare(strict_types=1);

namespace VendorName\RequestShield\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use VendorName\RequestShield\Facades\Shield;

final class ProtectRequest
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Shield::shouldBlock($request)) {
            if (Shield::shouldReturnView()) {
                return response()->view('request-shield::blocked', [
                    'request' => $request,
                ], 403);
            }

            throw new HttpException(403, 'Access Denied');
        }

        return $next($request);
    }
}
