<?php

namespace kak\CurrencyConverter\adapters;

class OpenExchangeRatesDataAdapter extends BaseDataAdapter
{
    public $apiKey = '';
    public $apiUrl = 'https://openexchangerates.org/api/latest.json';

    public function validateConfig(): bool
    {
        return $this->apiKey !== '';
    }

    public function get($base, $from = [], $reverse = false)
    {
        $endpoint = $this->buildUrl($this->apiUrl, [
            'app_id' => $this->apiKey,
            'show_alternative' => 'false'
        ]);

        $data = json_decode($this->client->get($endpoint), true);
        $rates = $data['rates'] ?? [];

        $result = [];
        if ($rates === []) {
            return $result;
        }

        $baseRate = $rates[$base];
        $newRates = [];

        foreach ($rates as $currency => $rateToUsd) {
            if ($currency === $base) {
                continue;
            }
            $value = $rateToUsd;
            if ($base !== 'USD') {
                $rate = $baseRate / $rateToUsd;
                $value =  (float)number_format($rate , 5, '.', '');
            }
            $newRates[$currency] = $this->formatResult($currency, 1, $value);
        }

        if ($from === []) {
            return $newRates;
        }

        foreach ($from as $currency) {
            if (isset($newRates[$currency])) {
                $result[$currency] = $newRates[$currency];
            }
        }

        return $result;
    }
}