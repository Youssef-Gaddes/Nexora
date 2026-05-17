<?php

namespace App\Service;

use App\Entity\Portfolio;
use Dompdf\Dompdf;
use Dompdf\Options;
use Twig\Environment;

class PortfolioReportService
{
    private Environment $twig;
    private SentimentAiService $aiService;

    public function __construct(Environment $twig, SentimentAiService $aiService)
    {
        $this->twig = $twig;
        $this->aiService = $aiService;
    }

    public function generatePortfolioPdf(Portfolio $portfolio): string
    {
        // 1. Prepare data for the report
        $assets = [];
        $totalMarketValue = 0.0;
        $totalCostBasis = 0.0;

        foreach ($portfolio->getPortfolioAssets() as $pa) {
            $mktPrice = $pa->getAsset()->getValue();
            $qty = $pa->getQuantity();
            $mktValue = $qty * $mktPrice;
            $cost = $qty * $pa->getAvgPrice();
            
            $profitLoss = $mktValue - $cost;
            $profitPct = $cost > 0 ? ($profitLoss / $cost) * 100 : 0;

            $assets[] = [
                'name' => $pa->getAsset()->getName(),
                'symbol' => $pa->getAsset()->getSymbol(),
                'quantity' => $qty,
                'price' => $mktPrice,
                'marketValue' => $mktValue,
                'profitLoss' => $profitLoss,
                'profitPct' => $profitPct
            ];

            $totalMarketValue += $mktValue;
            $totalCostBasis += $cost;
        }

        // 2. Get AI Advice
        $aiData = [];
        foreach ($assets as $a) {
            $aiData[] = [
                'symbol' => $a['symbol'],
                'quantity' => $a['quantity'],
                'price' => $a['price']
            ];
        }
        $aiAdvice = $this->aiService->getPortfolioAdvice($aiData, $totalMarketValue);

        // 3. Render HTML template
        $html = $this->twig->render('portfolio/report.html.twig', [
            'portfolio' => $portfolio,
            'assets' => $assets,
            'totalValue' => $totalMarketValue,
            'totalProfit' => $totalMarketValue - $totalCostBasis,
            'ai' => $aiAdvice
        ]);

        // 4. Configure Dompdf
        $options = new Options();
        $options->set('defaultFont', 'Helvetica');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // 5. Output the generated PDF
        return $dompdf->output();
    }
}
