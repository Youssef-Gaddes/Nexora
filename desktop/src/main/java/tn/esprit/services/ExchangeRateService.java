package tn.esprit.services;

import java.io.BufferedReader;
import java.io.InputStreamReader;
import java.math.BigDecimal;
import java.math.RoundingMode;
import java.net.HttpURLConnection;
import java.net.URL;
import java.util.HashMap;
import java.util.Map;

/**
 * Converts TND amounts to EUR, USD, and GBP using ExchangeRate-API.
 *
 * HOW IT WORKS:
 * - Makes one HTTP GET call to
 * https://v6.exchangerate-api.com/v6/{API_KEY}/latest/TND
 * - Parses the JSON response manually (no external JSON library needed)
 * - Caches the result for 1 hour to avoid spamming the API
 * - The free API key allows 1500 requests/month — more than enough
 *
 * USAGE:
 * ExchangeRateService fx = new ExchangeRateService();
 * BigDecimal inEuros = fx.convert(walletBalance, "EUR"); // e.g. 100 TND →
 * 29.50 EUR
 * String label = fx.formatAll(walletBalance); // "EUR 29.50 | USD 32.10 | GBP
 * 25.40"
 */
public class ExchangeRateService {

    private static final String API_KEY = "70f53387403b87ca9e7c9547";
    private static final String API_URL = "https://v6.exchangerate-api.com/v6/" + API_KEY + "/latest/TND";

    // 1-hour cache to avoid hammering the API
    private static Map<String, Double> cachedRates = null;
    private static long lastFetchTime = 0;
    private static final long CACHE_DURATION_MS = 3_600_000; // 1 hour

    /**
     * Fetches current TND → foreign currency rates (cached for 1 hour).
     * Returns an empty map on network failure — all conversions will return null
     * gracefully.
     */
    public Map<String, Double> getRates() {
        long now = System.currentTimeMillis();
        if (cachedRates != null && (now - lastFetchTime) < CACHE_DURATION_MS) {
            return cachedRates;
        }

        try {
            URL url = new URL(API_URL);
            HttpURLConnection conn = (HttpURLConnection) url.openConnection();
            conn.setRequestMethod("GET");
            conn.setConnectTimeout(5000);
            conn.setReadTimeout(5000);

            BufferedReader reader = new BufferedReader(new InputStreamReader(conn.getInputStream()));
            StringBuilder sb = new StringBuilder();
            String line;
            while ((line = reader.readLine()) != null)
                sb.append(line);
            reader.close();
            conn.disconnect();

            String json = sb.toString();
            Map<String, Double> rates = new HashMap<>();
            rates.put("EUR", extractRate(json, "EUR"));
            rates.put("USD", extractRate(json, "USD"));
            rates.put("GBP", extractRate(json, "GBP"));

            cachedRates = rates;
            lastFetchTime = now;
            System.out.println("ExchangeRateService: Rates refreshed (TND base).");
            return rates;

        } catch (Exception e) {
            System.err.println("ExchangeRateService: API error → " + e.getMessage());
            return new HashMap<>();
        }
    }

    /**
     * Converts a TND amount to the target currency.
     *
     * @param amountTND Amount in Tunisian Dinar
     * @param currency  "EUR", "USD", or "GBP"
     * @return Converted amount rounded to 2 decimal places, or null if unavailable
     */
    public BigDecimal convert(BigDecimal amountTND, String currency) {
        if (amountTND == null)
            return null;
        Double rate = getRates().get(currency);
        if (rate == null || rate == 0)
            return null;
        return amountTND.multiply(BigDecimal.valueOf(rate)).setScale(2, RoundingMode.HALF_UP);
    }

    /**
     * Returns a formatted multi-currency string for UI display.
     * Example: "€ 29.50 | $ 32.10 | £ 25.40"
     *
     * @param amountTND Wallet balance in TND
     */
    public String formatAll(BigDecimal amountTND) {
        BigDecimal eur = convert(amountTND, "EUR");
        BigDecimal usd = convert(amountTND, "USD");
        BigDecimal gbp = convert(amountTND, "GBP");

        return String.format("€ %s  |  $ %s  |  £ %s",
                eur != null ? eur.toPlainString() : "N/A",
                usd != null ? usd.toPlainString() : "N/A",
                gbp != null ? gbp.toPlainString() : "N/A");
    }

    // Parses a named field from raw JSON without a JSON library
    private double extractRate(String json, String currency) {
        try {
            String key = "\"" + currency + "\":";
            int idx = json.indexOf(key);
            if (idx == -1)
                return 0.0;
            int start = idx + key.length();
            int end = json.indexOf(",", start);
            if (end == -1)
                end = json.indexOf("}", start);
            return Double.parseDouble(json.substring(start, end).trim());
        } catch (Exception e) {
            return 0.0;
        }
    }
}
