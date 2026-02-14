<?php

namespace VendorName\RequestShield;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

final readonly class ShieldService
{
    private const DATETIME_FORMAT = 'Y-m-d H:i:s';

    public function __construct(
        private array $blockedIps = [],
        private array $blockedUserAgents = [],
        private array $whitelistedIps = [],
        private array $whitelistedUserAgents = [],
        private array $responseConfig = [],
        private array $loggingConfig = [],
        private array $statisticsConfig = [],
    ) {}

    public static function make(): self
    {
        $config = config('shield', []);

        return new self(
            blockedIps: $config['blocked_ips'] ?? [],
            blockedUserAgents: $config['blocked_user_agents'] ?? [],
            whitelistedIps: $config['whitelisted_ips'] ?? [],
            whitelistedUserAgents: $config['whitelisted_user_agents'] ?? [],
            responseConfig: $config['response'] ?? [],
            loggingConfig: $config['logging'] ?? [],
            statisticsConfig: $config['statistics'] ?? [],
        );
    }

    public function shouldBlock(Request $request): bool
    {
        $ip = $request->ip();
        $userAgent = $request->userAgent();

        if ($this->isWhitelisted($ip, $userAgent)) {
            return false;
        }

        return $this->isBlockedIp($ip) || $this->isBlockedUserAgent($userAgent);
    }

    public function isWhitelisted(?string $ip, ?string $userAgent): bool
    {
        if ($ip !== null && $this->matchesPatterns($ip, $this->whitelistedIps, false)) {
            return true;
        }

        if ($userAgent !== null && $this->matchesPatterns($userAgent, $this->whitelistedUserAgents, true)) {
            return true;
        }

        return false;
    }

    public function isBlockedIp(?string $ip): bool
    {
        return $ip !== null && $this->matchesPatterns($ip, $this->blockedIps, false);
    }

    public function isBlockedUserAgent(?string $userAgent): bool
    {
        return $userAgent !== null && $this->matchesPatterns($userAgent, $this->blockedUserAgents, true);
    }

    public function logBlockedRequest(Request $request): void
    {
        if (!($this->loggingConfig['enabled'] ?? true)) {
            return;
        }

        $data = [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'timestamp' => now()->format(self::DATETIME_FORMAT),
        ];

        if ($this->loggingConfig['include_headers'] ?? true) {
            $data['headers'] = $request->headers->all();
        }

        Log::channel($this->loggingConfig['channel'] ?? 'daily')
            ->warning('Request blocked by RequestShield', $data);
    }

    public function recordBlock(): void
    {
        $driver = $this->statisticsConfig['driver'] ?? 'file';

        match ($driver) {
            'file' => $this->recordBlockToFile(),
            'memory' => $this->recordBlockToMemory(),
            default => null,
        };
    }

    public function getDailyStats(): array
    {
        $driver = $this->statisticsConfig['driver'] ?? 'file';

        return match ($driver) {
            'file' => $this->getStatsFromFile(),
            'memory' => $this->getStatsFromMemory(),
            default => ['blocked_count' => 0, 'date' => today()->toDateString()],
        };
    }

    private function matchesPatterns(string $value, array $patterns, bool $isRegex): bool
    {
        foreach ($patterns as $pattern) {
            if ($isRegex && $this->isValidRegex($pattern)) {
                if (preg_match($pattern, $value)) {
                    return true;
                }
            } else {
                if (stripos($value, $pattern) !== false) {
                    return true;
                }
            }
        }

        return false;
    }

    private function isValidRegex(string $pattern): bool
    {
        return str_starts_with($pattern, '/') && @preg_match($pattern, '') !== false;
    }

    private function recordBlockToFile(): void
    {
        $path = $this->statisticsConfig['file_path'] ?? storage_path('framework/shield-stats.json');
        $today = today()->toDateString();
        $stats = $this->readStatsFile($path);

        if (!isset($stats[$today])) {
            $stats[$today] = ['blocked_count' => 0, 'requests' => []];
        }

        $stats[$today]['blocked_count']++;

        $this->writeStatsFile($path, $stats);
    }

    private function recordBlockToMemory(): void
    {
        static $memory = [];
        $today = today()->toDateString();

        if (!isset($memory[$today])) {
            $memory[$today] = 0;
        }

        $memory[$today]++;
    }

    private function getStatsFromFile(): array
    {
        $path = $this->statisticsConfig['file_path'] ?? storage_path('framework/shield-stats.json');
        $today = today()->toDateString();
        $stats = $this->readStatsFile($path);

        return $stats[$today] ?? ['blocked_count' => 0, 'date' => $today];
    }

    private function getStatsFromMemory(): array
    {
        static $memory = [];
        $today = today()->toDateString();

        return [
            'blocked_count' => $memory[$today] ?? 0,
            'date' => $today,
        ];
    }

    private function readStatsFile(string $path): array
    {
        if (!file_exists($path)) {
            return [];
        }

        $content = file_get_contents($path);

        return json_decode($content, true) ?? [];
    }

    private function writeStatsFile(string $path, array $stats): void
    {
        $directory = dirname($path);

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        file_put_contents($path, json_encode($stats, JSON_PRETTY_PRINT));
    }
}