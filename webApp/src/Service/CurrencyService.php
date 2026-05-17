<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class CurrencyService
{
    private HttpClientInterface $httpClient;
    private CacheInterface $cache;
    private const API_URL = 'https://api.frankfurter.app/latest?from=USD&to=TND';

    public function __construct(HttpClientInterface $httpClient, CacheInterface $cache)
    {
        $this->httpClient = $httpClient;
        $this->cache = $cache;
    }

    /**
     * Gets the current USD to TND exchange rate.
     * Cached for 24 hours to ensure performance.
     */
    public function getUsdTndRate(): float
    {
        return $this->cache->get('usd_tnd_exchange_rate', function (ItemInterface $item) {
            $item->expiresAfter(86400); // 24 hours

            try {
                $response = $this->httpClient->request('GET', self::API_URL);
                if ($response->getStatusCode() !== 200) {
                    return 3.15; // Default fallback for TND/USD
                }

                $data = $response->toArray();
                return (float)($data['rates']['TND'] ?? 3.15);
            } catch (\Exception $e) {
                // Log error if needed
                return 3.15; // Safe fallback
            }
        });
    }
}
