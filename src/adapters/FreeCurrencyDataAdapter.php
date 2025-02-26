<?php

namespace kak\CurrencyConverter\adapters;


/**
 * Class FreeCurrencyDataAdapter
 * @package kak\CurrencyConverter\adapters
 * @docs
 * https://free.currencyconverterapi.com/api/v5/currencies
 * https://free.currencyconverterapi.com/api/v5/convert?q=USD_PHP,PHP_USD&compact=ultra
 * free per 2 request in 1 ses;
 *
 * update its free use - deprecate
 *
 */
class FreeCurrencyDataAdapter extends BaseDataAdapter
{
    public $provider = 'FreeCurrency';

    public $apiUrl = 'https://free.currconv.com/api/v7/convert';
    public $apiKey = '';
    public $chunkSize = 4;

    private function getApiData($base, $from)
    {
        $query = [];
        foreach ($from as $code) {
            $query[] = sprintf('%s_%s', $base, $code);
        }
        $urlParams = ['q' => implode(',', $query), 'compact' => 'ultra'];
        if (!empty($this->apiKey)) {
            $urlParams['apiKey'] = $this->apiKey;
        }
        $url = $this->buildUrl($this->apiUrl, $urlParams);

        try {
            $data = $this->client->get($url);
            $jsonData = json_decode($data, true);
            if (!$jsonData) {
                return false;
            }
        } catch (\Exception  $e) {
            return false;
        }

        return $jsonData;
    }


    public function get($base, $from = [], $reverse = false)
    {
        $query = [];
        foreach ($from as $code) {
            $query[] = sprintf('%s_%s', $base, $code);
        }

        $rates = [];
        $useFreeChunk = !empty($this->apiKey);

        if(!$useFreeChunk){
            return false;
        }

        $chunks = array_chunk($from, $this->chunkSize);
        foreach ($chunks as $chunk) {
            $chunkData = $this->getApiData($base, $chunk);
            if ($chunkData) {
                $rates = array_merge($rates, $chunkData);
            }
        }

        $skip = is_array($from) && count($from) > 0;
        $result = [];
        foreach ($rates as $code => $rate) {
            list($baseCode, $rateCode) = explode('_', $code);
            if ($skip && !in_array($rateCode, $from)) {
                continue;
            }
            // 1 base to rate code
            $exchange = $rate;
            // 1 code to base
            if ($reverse === true) {
                $exchange = 1 / $exchange;
            }
            $result[$rateCode] = $this->formatResult($rateCode, 1, $exchange);
        }
        return $result;
    }


    public function validateConfig(): bool
    {
        return $this->apiKey !== '';
    }
}