<?php

namespace VendorName\RequestShield\Tests\Feature;

use VendorName\RequestShield\Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use VendorName\RequestShield\Middleware\ProtectRequest;

class MiddlewareTest extends TestCase
{
    private ProtectRequest $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new ProtectRequest(
            $this->app->make(\VendorName\RequestShield\ShieldService::class)
        );
    }

    /** @test */
    public function it_passes_through_non_blocked_requests(): void
    {
        $request = Request::create('/test', 'GET');
        $request->server->set('REMOTE_ADDR', '192.168.1.1');
        $request->headers->set('User-Agent', 'Mozilla/5.0');

        $response = $this->middleware->handle($request, fn ($r) => new Response('OK'));

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', $response->getContent());
    }

    /** @test */
    public function it_blocks_blocked_ip_in_abort_mode(): void
    {
        $this->app['config']->set('shield.blocked_ips', ['10.0.0.1']);
        $this->app['config']->set('shield.response_mode', 'abort');

        $request = Request::create('/test', 'GET');
        $request->server->set('REMOTE_ADDR', '10.0.0.1');
        $request->headers->set('User-Agent', 'Mozilla/5.0');

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->middleware->handle($request, fn ($r) => new Response('OK'));
    }

    /** @test */
    public function it_blocks_blocked_user_agent_in_abort_mode(): void
    {
        $this->app['config']->set('shield.blocked_user_agents', ['/^badbot/i']);
        $this->app['config']->set('shield.response_mode', 'abort');

        $request = Request::create('/test', 'GET');
        $request->server->set('REMOTE_ADDR', '192.168.1.1');
        $request->headers->set('User-Agent', 'BadBot/1.0');

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->middleware->handle($request, fn ($r) => new Response('OK'));
    }

    /** @test */
    public function it_returns_view_in_view_mode(): void
    {
        $this->app['config']->set('shield.blocked_ips', ['10.0.0.1']);
        $this->app['config']->set('shield.response_mode', 'view');

        $request = Request::create('/test', 'GET');
        $request->server->set('REMOTE_ADDR', '10.0.0.1');
        $request->headers->set('User-Agent', 'Mozilla/5.0');

        $response = $this->middleware->handle($request, fn ($r) => new Response('OK'));

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertStringContainsString('Access Denied', $response->getContent());
    }

    /** @test */
    public function it_records_blocked_request(): void
    {
        $this->app['config']->set('shield.blocked_ips', ['10.0.0.1']);

        $service = $this->app->make(\VendorName\RequestShield\ShieldService::class);
        $middleware = new ProtectRequest($service);

        $request = Request::create('/test', 'GET');
        $request->server->set('REMOTE_ADDR', '10.0.0.1');
        $request->headers->set('User-Agent', 'TestBot/1.0');

        try {
            $middleware->handle($request, fn ($r) => new Response('OK'));
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
        }

        $stats = $service->getStats();
        $this->assertEquals(1, $stats['blocked_count']);
    }
}