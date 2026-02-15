<?php

declare(strict_types=1);

namespace VendorName\RequestShield\Tests\Feature;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpKernel\Exception\HttpException;
use VendorName\RequestShield\Middleware\ProtectRequest;
use VendorName\RequestShield\ShieldService;
use VendorName\RequestShield\Tests\TestCase;

final class MiddlewareTest extends TestCase
{
    #[Test]
    public function it_allows_clean_requests(): void
    {
        $this->app['config']->set('shield.blocked_ips', ['10.0.0.1']);
        $this->app['config']->set('shield.blocked_user_agents', ['BadBot']);
        $this->app->forgetInstance(ShieldService::class);

        $request = Request::create('/test', 'GET', server: [
            'REMOTE_ADDR' => '8.8.8.8',
            'HTTP_USER_AGENT' => 'Mozilla/5.0',
        ]);

        $middleware = $this->app->make(ProtectRequest::class);

        $response = $middleware->handle($request, function () {
            return new Response('OK');
        });

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', $response->getContent());
    }

    #[Test]
    public function it_blocks_a_blacklisted_ip_with_abort(): void
    {
        $this->app['config']->set('shield.blocked_ips', ['10.0.0.1']);
        $this->app['config']->set('shield.blocked_user_agents', []);
        $this->app['config']->set('shield.response_mode', 'abort');
        $this->app->forgetInstance(ShieldService::class);

        $request = Request::create('/test', 'GET', server: [
            'REMOTE_ADDR' => '10.0.0.1',
            'HTTP_USER_AGENT' => 'Mozilla/5.0',
        ]);

        $middleware = $this->app->make(ProtectRequest::class);

        $this->expectException(HttpException::class);

        $middleware->handle($request, function () {
            return new Response('OK');
        });
    }

    #[Test]
    public function it_blocks_a_bad_user_agent_with_abort(): void
    {
        $this->app['config']->set('shield.blocked_ips', []);
        $this->app['config']->set('shield.blocked_user_agents', ['BadBot']);
        $this->app['config']->set('shield.response_mode', 'abort');
        $this->app->forgetInstance(ShieldService::class);

        $request = Request::create('/test', 'GET', server: [
            'REMOTE_ADDR' => '8.8.8.8',
            'HTTP_USER_AGENT' => 'BadBot/1.0',
        ]);

        $middleware = $this->app->make(ProtectRequest::class);

        $this->expectException(HttpException::class);

        $middleware->handle($request, function () {
            return new Response('OK');
        });
    }

    #[Test]
    public function it_blocks_with_view_response(): void
    {
        $this->app['config']->set('shield.blocked_ips', ['10.0.0.1']);
        $this->app['config']->set('shield.blocked_user_agents', []);
        $this->app['config']->set('shield.response_mode', 'view');
        $this->app['config']->set('shield.blocked_view', 'request-shield::blocked');
        $this->app->forgetInstance(ShieldService::class);

        $request = Request::create('/test', 'GET', server: [
            'REMOTE_ADDR' => '10.0.0.1',
            'HTTP_USER_AGENT' => 'Mozilla/5.0',
        ]);

        $middleware = $this->app->make(ProtectRequest::class);

        $response = $middleware->handle($request, function () {
            return new Response('OK');
        });

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertStringContainsString('Access denied', $response->getContent());
    }

    #[Test]
    public function it_increments_blocked_count_on_block(): void
    {
        $this->app['config']->set('shield.blocked_ips', ['10.0.0.1']);
        $this->app['config']->set('shield.blocked_user_agents', []);
        $this->app['config']->set('shield.response_mode', 'view');
        $this->app->forgetInstance(ShieldService::class);

        $request = Request::create('/test', 'GET', server: [
            'REMOTE_ADDR' => '10.0.0.1',
            'HTTP_USER_AGENT' => 'Mozilla/5.0',
        ]);

        $middleware = $this->app->make(ProtectRequest::class);

        $middleware->handle($request, function () {
            return new Response('OK');
        });

        /** @var ShieldService $service */
        $service = $this->app->make(ShieldService::class);

        $this->assertEquals(1, $service->getBlockedCount());
    }
}
