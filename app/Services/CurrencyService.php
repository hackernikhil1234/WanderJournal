<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CurrencyService
{
    private string $apiKey;
    private string $cachePrefix = 'exchange_rates_';
    private int $cacheTtl = 21600; // 6 hours

    public function __construct()
    {
        $this->apiKey = env('EXCHANGE_RATE_API_KEY', '');
    }

    /**
     * Convert an amount from one currency to another.
     */
    public function convert(float $amount, string $from, string $to): float
    {
        if ($from === $to) return $amount;

        $rates = $this->getRates($from);
        if (!$rates || !isset($rates[$to])) {
            Log::warning("Currency conversion failed: {$from} to {$to}");
            return $amount; // Return unchanged if conversion fails
        }

        return round($amount * $rates[$to], 2);
    }

    /**
     * Get exchange rates for a base currency (cached).
     */
    public function getRates(string $base = 'USD'): ?array
    {
        $cacheKey = $this->cachePrefix . $base;

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($base) {
            // Try free ExchangeRate-API
            if (!empty($this->apiKey) && $this->apiKey !== 'YOUR_EXCHANGERATE_API_KEY') {
                return $this->fetchFromExchangeRateApi($base);
            }

            // Fallback: static approximate rates for common currencies
            return $this->getFallbackRates($base);
        });
    }

    /**
     * Get supported currencies with their names and symbols.
     */
    public function getSupportedCurrencies(): array
    {
        return [
            'USD' => ['name' => 'US Dollar', 'symbol' => '$'],
            'EUR' => ['name' => 'Euro', 'symbol' => '€'],
            'GBP' => ['name' => 'British Pound', 'symbol' => '£'],
            'JPY' => ['name' => 'Japanese Yen', 'symbol' => '¥'],
            'INR' => ['name' => 'Indian Rupee', 'symbol' => '₹'],
            'AUD' => ['name' => 'Australian Dollar', 'symbol' => 'A$'],
            'CAD' => ['name' => 'Canadian Dollar', 'symbol' => 'C$'],
            'CHF' => ['name' => 'Swiss Franc', 'symbol' => 'Fr'],
            'CNY' => ['name' => 'Chinese Yuan', 'symbol' => '¥'],
            'SGD' => ['name' => 'Singapore Dollar', 'symbol' => 'S$'],
            'AED' => ['name' => 'UAE Dirham', 'symbol' => 'د.إ'],
            'THB' => ['name' => 'Thai Baht', 'symbol' => '฿'],
            'MYR' => ['name' => 'Malaysian Ringgit', 'symbol' => 'RM'],
            'IDR' => ['name' => 'Indonesian Rupiah', 'symbol' => 'Rp'],
            'PHP' => ['name' => 'Philippine Peso', 'symbol' => '₱'],
            'KRW' => ['name' => 'South Korean Won', 'symbol' => '₩'],
            'MXN' => ['name' => 'Mexican Peso', 'symbol' => '$'],
            'BRL' => ['name' => 'Brazilian Real', 'symbol' => 'R$'],
            'ZAR' => ['name' => 'South African Rand', 'symbol' => 'R'],
            'TRY' => ['name' => 'Turkish Lira', 'symbol' => '₺'],
        ];
    }

    /**
     * Format an amount with currency symbol.
     */
    public function format(float $amount, string $currency): string
    {
        $currencies = $this->getSupportedCurrencies();
        $symbol = $currencies[$currency]['symbol'] ?? $currency . ' ';
        return $symbol . number_format($amount, 2);
    }

    private function fetchFromExchangeRateApi(string $base): ?array
    {
        try {
            $response = Http::timeout(10)
                ->get("https://v6.exchangerate-api.com/v6/{$this->apiKey}/latest/{$base}");

            if ($response->successful()) {
                return $response->json('conversion_rates');
            }
        } catch (\Exception $e) {
            Log::error('ExchangeRate API error: ' . $e->getMessage());
        }

        return null;
    }

    private function getFallbackRates(string $base): array
    {
        // Approximate rates relative to USD (updated periodically)
        $usdRates = [
            'USD' => 1.0, 'EUR' => 0.92, 'GBP' => 0.79, 'JPY' => 149.5,
            'INR' => 83.2, 'AUD' => 1.53, 'CAD' => 1.36, 'CHF' => 0.88,
            'CNY' => 7.24, 'SGD' => 1.34, 'AED' => 3.67, 'THB' => 35.1,
            'MYR' => 4.72, 'IDR' => 15800, 'PHP' => 56.4, 'KRW' => 1325,
            'MXN' => 17.2, 'BRL' => 4.95, 'ZAR' => 18.6, 'TRY' => 32.1,
        ];

        if ($base === 'USD') {
            return $usdRates;
        }

        // Cross-currency conversion via USD
        $baseToUsd = isset($usdRates[$base]) ? (1 / $usdRates[$base]) : 1;
        $result = [];
        foreach ($usdRates as $currency => $rate) {
            $result[$currency] = round($rate * $baseToUsd, 6);
        }
        return $result;
    }
}
