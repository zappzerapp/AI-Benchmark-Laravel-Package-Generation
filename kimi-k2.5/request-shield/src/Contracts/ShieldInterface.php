<?php

declare(strict_types=1);

namespace VendorName\RequestShield\Contracts;

use Illuminate\Http\Request;

interface ShieldInterface
{
    /**
     * Prüft, ob ein Request blockiert werden soll.
     */
    public function isBlocked(Request $request): bool;

    /**
     * Prüft, ob die IP-Adresse blockiert ist.
     */
    public function isBlockedIp(string $ip): bool;

    /**
     * Prüft, ob der User-Agent blockiert ist.
     */
    public function isBlockedUserAgent(?string $userAgent): bool;

    /**
     * Gibt die Liste der blockierten IPs zurück.
     *
     * @return array<string>
     */
    public function getBlockedIps(): array;

    /**
     * Gibt die Liste der blockierten User-Agents zurück.
     *
     * @return array<string>
     */
    public function getBlockedUserAgents(): array;

    /**
     * Fügt eine IP zur Blockierliste hinzu (runtime only).
     */
    public function addBlockedIp(string $ip): void;

    /**
     * Fügt einen User-Agent zur Blockierliste hinzu (runtime only).
     */
    public function addBlockedUserAgent(string $userAgent): void;

    /**
     * Inkrementiert den Zähler für blockierte Requests.
     */
    public function incrementBlockedCount(): void;

    /**
     * Gibt die Anzahl der heute blockierten Requests zurück.
     */
    public function getTodayBlockedCount(): int;
}