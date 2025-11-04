<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class GeolocationService
{
    /**
     * Get location data for IP address
     */
    public function getLocation(string $ipAddress): array
    {
        // Cache for 1 hour to avoid API rate limits
        $cacheKey = 'geo_' . str_replace('.', '_', $ipAddress);

        return Cache::remember($cacheKey, 3600, function () use ($ipAddress) {
            try {
                // Try ipinfo.io first (free tier: 1000 requests/month)
                $response = Http::timeout(10)
                    ->get("https://ipinfo.io/{$ipAddress}/json");

                if ($response->successful()) {
                    $data = $response->json();

                    return [
                        'ip' => $data['ip'] ?? $ipAddress,
                        'country' => $data['country'] ?? 'Unknown',
                        'city' => $data['city'] ?? 'Unknown',
                        'region' => $data['region'] ?? 'Unknown',
                        'org' => $data['org'] ?? 'Unknown',
                        'timezone' => $data['timezone'] ?? 'Unknown',
                        'source' => 'ipinfo.io'
                    ];
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to get geolocation from ipinfo.io', [
                    'ip' => $ipAddress,
                    'error' => $e->getMessage()
                ]);
            }

            // Fallback: Try ip-api.com (free, no API key required)
            try {
                $response = Http::timeout(10)
                    ->get("http://ip-api.com/json/{$ipAddress}");

                if ($response->successful() && ($response->json('status') === 'success')) {
                    $data = $response->json();

                    return [
                        'ip' => $data['query'] ?? $ipAddress,
                        'country' => $data['country'] ?? 'Unknown',
                        'city' => $data['city'] ?? 'Unknown',
                        'region' => $data['regionName'] ?? 'Unknown',
                        'org' => $data['isp'] ?? 'Unknown',
                        'timezone' => $data['timezone'] ?? 'Unknown',
                        'source' => 'ip-api.com'
                    ];
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to get geolocation from ip-api.com', [
                    'ip' => $ipAddress,
                    'error' => $e->getMessage()
                ]);
            }

            // Ultimate fallback: Basic info only
            return [
                'ip' => $ipAddress,
                'country' => 'Unknown',
                'city' => 'Unknown',
                'region' => 'Unknown',
                'org' => 'Unknown',
                'timezone' => 'Unknown',
                'source' => 'fallback'
            ];
        });
    }

    /**
     * Check if IP is private/internal
     */
    public function isPrivateIp(string $ipAddress): bool
    {
        return in_array($ipAddress, [
            '127.0.0.1',        // localhost
            '::1',               // localhost IPv6
            '192.168.0.1',      // common router
            '10.0.0.1',          // private network
        ]) ||
        $this->ipInRange($ipAddress, '192.168.0.0', '192.168.255.255') ||  // Private network
        $this->ipInRange($ipAddress, '10.0.0.0', '10.255.255.255') ||      // Private network
        $this->ipInRange($ipAddress, '172.16.0.0', '172.31.255.255');      // Private network
    }

    /**
     * Check if IP is in range
     */
    private function ipInRange(string $ip, string $rangeStart, string $rangeEnd): bool
    {
        $ipLong = ip2long($ip);
        $startLong = ip2long($rangeStart);
        $endLong = ip2long($rangeEnd);

        return $ipLong !== false && $startLong !== false && $endLong !== false &&
               $ipLong >= $startLong && $ipLong <= $endLong;
    }

    /**
     * Get ISP information
     */
    public function getIspInfo(string $ipAddress): string
    {
        $location = $this->getLocation($ipAddress);
        return $location['org'] ?? 'Unknown';
    }

    /**
     * Get country information
     */
    public function getCountry(string $ipAddress): string
    {
        $location = $this->getLocation($ipAddress);
        return $location['country'] ?? 'Unknown';
    }

    /**
     * Check if IP is from VPN/Proxy (basic detection)
     */
    public function isSuspiciousIp(string $ipAddress): bool
    {
        // This is a basic implementation - you could integrate with commercial VPN detection services
        $location = $this->getLocation($ipAddress);

        $suspiciousIndicators = [
            'VPN' => true,
            'proxy' => true,
            'hosting' => true,
            'data center' => true,
        ];

        $org = strtolower($location['org'] ?? '');

        foreach ($suspiciousIndicators as $indicator => $value) {
            if (strpos($org, $indicator) !== false) {
                return true;
            }
        }

        return false;
    }
}