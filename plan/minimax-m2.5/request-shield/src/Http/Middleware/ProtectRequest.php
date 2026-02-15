<?php

namespace VendorName\RequestShield\Http\Middleware;

use VendorName\RequestShield\Support\ShieldService;

class ProtectRequest
{
    private ShieldService $shield;

    public function __construct(ShieldService $shield)
    {
        $this->shield = $shield;
    }

    public function handle($request, \Closure $next)
    {
        if (!$this->shield->isEnabled()) {
            return $next($request);
        }

        if ($this->shield->shouldBlockRequest($request)) {
            $this->shield->recordBlock('ip', $request->ip());
            return new \Illuminate\Http\Response(
                'Access denied',
                $this->shield->getResponseStatus()
            );
        }

        return $next($request);
    }
}
