<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeolocationService
{
    protected string $baseUrl = 'http://ip-api.com/json/';

    protected int $cacheTime = 86400; // 24 hours

    /**
     * Get geolocation data for an IP address.
     */
    public function getLocationData(string $ipAddress): array
    {
        // Don't geolocate local/private IPs
        if ($this->isLocalIp($ipAddress)) {
            return $this->getLocalLocationData();
        }

        $cacheKey = "geolocation.{$ipAddress}";

        return Cache::remember($cacheKey, $this->cacheTime, function () use ($ipAddress) {
            return $this->fetchLocationData($ipAddress);
        });
    }

    /**
     * Fetch location data from the geolocation API.
     */
    protected function fetchLocationData(string $ipAddress): array
    {
        try {
            $response = Http::timeout(5)->get($this->baseUrl.$ipAddress);

            if ($response->successful()) {
                $data = $response->json();

                if ($data['status'] === 'success') {
                    return [
                        'country' => $data['country'] ?? null,
                        'city' => $data['city'] ?? null,
                        'latitude' => $data['lat'] ?? null,
                        'longitude' => $data['lon'] ?? null,
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::warning('Geolocation API failed', [
                'ip' => $ipAddress,
                'error' => $e->getMessage(),
            ]);
        }

        return $this->getDefaultLocationData();
    }

    /**
     * Check if an IP address is local/private.
     */
    protected function isLocalIp(string $ipAddress): bool
    {
        return in_array($ipAddress, ['127.0.0.1', '::1', 'localhost']) ||
               filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;
    }

    /**
     * Get location data for local/development environments.
     */
    protected function getLocalLocationData(): array
    {
        return [
            'country' => 'Local',
            'city' => 'Development',
            'latitude' => null,
            'longitude' => null,
        ];
    }

    /**
     * Get default location data when geolocation fails.
     */
    protected function getDefaultLocationData(): array
    {
        return [
            'country' => null,
            'city' => null,
            'latitude' => null,
            'longitude' => null,
        ];
    }

    /**
     * Check if a location is suspicious (e.g., different country).
     */
    public function isLocationSuspicious(string $ipAddress, ?string $lastCountry = null): bool
    {
        if (! $lastCountry) {
            return false;
        }

        $currentLocation = $this->getLocationData($ipAddress);

        // Consider it suspicious if the country changed and it's not a local IP
        return $currentLocation['country'] &&
               $currentLocation['country'] !== $lastCountry &&
               ! $this->isLocalIp($ipAddress);
    }

    /**
     * Get location summary string for logging.
     */
    public function getLocationSummary(string $ipAddress): string
    {
        $location = $this->getLocationData($ipAddress);

        if ($location['city'] && $location['country']) {
            return "{$location['city']}, {$location['country']}";
        }

        if ($location['country']) {
            return $location['country'];
        }

        return 'Unknown location';
    }
}
