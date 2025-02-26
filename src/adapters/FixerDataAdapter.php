<?php

namespace kak\CurrencyConverter\adapters;

class FixerDataAdapter extends BaseDataAdapter
{
    public $provider = 'fixer';

    public $apiKey = '';
    public $apiUrl = 'http://data.fixer.io/api/latest';

    public function get($base, $from = [], $reverse = false)
    {
        $url = $this->buildUrl($this->apiUrl, [
            'base' => $base,
            'access_key' => $this->apiKey,
            'symbols' => implode(',',$from)
        ]);
        try {
            $data = $this->client->get($url);
            $jsonData = json_decode($data ,true);
        } catch (\Exception  $e) {
            return false;
        }
        $result = [];

        $skip = is_array($from) && count($from) > 0 ;
        $rates = isset($jsonData['rates']) ? $jsonData['rates'] : [];

        foreach($rates as $code => $rate){
            if($skip && !in_array($code,$from)){
                continue;
            }
            // 1 base to rate code
            $exchange = $rate;
            // 1 code to base
            if($reverse === true){
                $exchange = 1 / $exchange;
            }
            $result[$code] = $this->formatResult($code, 1, $exchange);
        }
        return $result;
    }

    public function validateConfig(): bool
    {
        return $this->apiKey !== '';
    }
}