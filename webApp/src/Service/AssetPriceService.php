<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Repository\AssetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class AssetPriceService
{
    private HttpClientInterface $client;
    private EntityManagerInterface $entityManager;
    private AssetRepository $assetRepository;
    private LoggerInterface $logger;
    private CurrencyService $currencyService;

    public function __construct(
        HttpClientInterface $client,
        EntityManagerInterface $entityManager,
        AssetRepository $assetRepository,
        LoggerInterface $logger,
        CurrencyService $currencyService
    ) {
        $this->client = $client;
        $this->entityManager = $entityManager;
        $this->assetRepository = $assetRepository;
        $this->logger = $logger;
        $this->currencyService = $currencyService;
    }

    /**
     * Updates the local database 'value' for assets against CoinGecko.
     * We map typical symbols (BTC, ETH, etc) to CoinGecko IDs.
     */
    public function syncAssetPrices(): int
    {
        $assets = $this->assetRepository->findAll();
        if (count($assets) === 0) {
            return 0;
        }

        $symbolMap = [
            'BTC' => 'bitcoin',
            'ETH' => 'ethereum',
            'BNB' => 'binancecoin',
            'SOL' => 'solana',
            'XRP' => 'ripple',
            'ADA' => 'cardano',
            'DOGE'=> 'dogecoin',
            'AVAX'=> 'avalanche-2',
            'LINK'=> 'chainlink',
            'DOT' => 'polkadot'
        ];

        // Collect ids for the api response
        $idsToFetch = [];
        $assetMap = []; // symbol -> App\Entity\Asset entity
        foreach ($assets as $asset) {
            $sym = strtoupper($asset->getSymbol());
            if (isset($symbolMap[$sym])) {
                $idsToFetch[] = $symbolMap[$sym];
                $assetMap[$symbolMap[$sym]] = $asset;
            }
        }

        if (empty($idsToFetch)) {
            return 0;
        }

        try {
            $response = $this->client->request(
                'GET',
                'https://api.coingecko.com/api/v3/simple/price',
                [
                    'query' => [
                        'ids' => implode(',', $idsToFetch),
                        'vs_currencies' => 'usd', // assuming TND/USD parity strictly for demo or we just use USD
                    ]
                ]
            );

            $data = $response->toArray();
            $updatedCount = 0;
            $tndRate = $this->currencyService->getUsdTndRate();

            foreach ($data as $coinId => $currencyData) {
                if (isset($currencyData['usd']) && isset($assetMap[$coinId])) {
                    $asset = $assetMap[$coinId];
                    // Convert USD to TND before saving
                    $priceInTnd = (float)$currencyData['usd'] * $tndRate;
                    $asset->setValue($priceInTnd);
                    $updatedCount++;
                }
            }

            $this->entityManager->flush();
            return $updatedCount;
        } catch (\Exception $e) {
            $this->logger->error('Failed to sync asset prices from CoinGecko: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Recalculates the total value for all portfolios based on current asset prices.
     */
    public function recalculatePortfolios(): void
    {
        $portfolios = $this->entityManager->getRepository(\App\Entity\Portfolio::class)->findAll();

        foreach ($portfolios as $portfolio) {
            $totalVal = 0.0;
            foreach ($portfolio->getPortfolioAssets() as $pa) {
                $totalVal += $pa->getQuantity() * $pa->getAsset()->getValue();
            }
            $portfolio->setTotalValue($totalVal);
        }

        $this->entityManager->flush();
    }
}
