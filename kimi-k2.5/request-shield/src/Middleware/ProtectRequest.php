<?php

declare(strict_types=1);

namespace VendorName\RequestShield\Middleware;

use Closure;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use VendorName\RequestShield\Contracts\ShieldInterface;
use VendorName\RequestShield\Exceptions\ShieldException;

final readonly class ProtectRequest
{
    public function __construct(
        private ShieldInterface $shield,
        private Repository $config,
        private Factory $view
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->shield->isBlocked($request)) {
            return $this->blockedResponse($request);
        }

        return $next($request);
    }

    private function blockedResponse(Request $request): Response
    {
        $statusCode = $this->config->get('shield.response.status_code', 403);
        $viewName = $this->config->get('shield.response.view');
        $message = $this->config->get('shield.response.message', 'Access denied.');

        if ($viewName && $this->view->exists($viewName)) {
            $content = $this->view->make($viewName, [
                'message' => $message,
                'ip' => $request->ip(),
                'userAgent' => $request->userAgent(),
            ])->render();

            return new Response($content, $statusCode);
        }

        throw new ShieldException($message, $statusCode);
    }
}