<?php

namespace VendorName\RequestShield\Tests\Unit;

use PHPUnit\Framework\TestCase;
use VendorName\RequestShield\Http\Middleware\ProtectRequest;
use VendorName\RequestShield\Support\ShieldService;
use Illuminate\Http\Request;

class ProtectRequestTest extends TestCase
{
    public function test_passes_when_shield_not_enabled(): void
    {
        $shield = $this->createMock(ShieldService::class);
        $shield->method('isEnabled')->willReturn(false);
        $shield->method('shouldBlockRequest')->willReturn(false);

        $middleware = new ProtectRequest($shield);
        $request = $this->createMockRequest('192.168.1.1', 'Mozilla/5.0');

        $response = $middleware->handle($request, function () {
            return new \Illuminate\Http\Response('ok', 200);
        });

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_passes_when_request_not_blocked(): void
    {
        $shield = $this->createMock(ShieldService::class);
        $shield->method('isEnabled')->willReturn(true);
        $shield->method('shouldBlockRequest')->willReturn(false);

        $middleware = new ProtectRequest($shield);
        $request = $this->createMockRequest('192.168.1.1', 'Mozilla/5.0');

        $response = $middleware->handle($request, function () {
            return new \Illuminate\Http\Response('ok', 200);
        });

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_blocks_blocked_ip(): void
    {
        $shield = $this->createMock(ShieldService::class);
        $shield->method('isEnabled')->willReturn(true);
        $shield->method('shouldBlockRequest')->willReturn(true);
        $shield->method('getResponseStatus')->willReturn(403);
        $shield->method('getResponseView')->willReturn('request-shield::blocked');
        $shield->method('recordBlock');

        $middleware = new ProtectRequest($shield);
        $request = $this->createMockRequest('192.168.1.100', 'Mozilla/5.0');

        $response = $middleware->handle($request, function () {
            return new \Illuminate\Http\Response('ok', 200);
        });

        $this->assertEquals(403, $response->getStatusCode());
    }

    public function test_records_blocked_stats(): void
    {
        $shield = $this->createMock(ShieldService::class);
        $shield->method('isEnabled')->willReturn(true);
        $shield->method('shouldBlockRequest')->willReturn(true);
        $shield->method('getResponseStatus')->willReturn(403);
        $shield->method('getResponseView')->willReturn('request-shield::blocked');
        $shield->expects($this->once())->method('recordBlock');

        $middleware = new ProtectRequest($shield);
        $request = $this->createMockRequest('192.168.1.100', 'curl');

        $middleware->handle($request, function () {
            return response()->json(['ok' => true]);
        });
    }

    private function createMockRequest(string $ip, string $userAgent): object
    {
        return new class($ip, $userAgent) {
            public function __construct(public string $ip, public string $userAgent) {}
            public function ip(): string { return $this->ip; }
            public function userAgent(): string { return $this->userAgent; }
        };
    }
}
