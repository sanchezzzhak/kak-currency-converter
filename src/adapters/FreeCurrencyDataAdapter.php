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
 */
class FreeCurrencyDataAdapter extends BaseDataAdapter
{
    public $provider = 'FreeCurrency';

    public $apiUrl = 'https://free.currencyconverterapi.com/api/v5/convert';
    public $apiKey;


    private function getApiData($base, $from)
    {
        $query = [];
        foreach ($from as $code){
            $query[] = sprintf('%s_%s', $base, $code);
        }
        $urlParams = ['q' => implode(',', $query), 'compact' => 'ultra' ];
        if(!empty($this->apiKey)){
            $urlParams['apiKey'] = $this->apiKey;
        }
        $url = $this->buildUrl($this->apiUrl, $urlParams);
        try {
            $data = $this->client->get($url);
            if(!$jsonData = json_decode($data ,true)){
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
        foreach ($from as $code){
            $query[] = sprintf('%s_%s', $base, $code);
        }

        $rates = [];
        $useFreeChunk = empty($this->apiKey);
        if($useFreeChunk){
             $chunks = array_chunk($from, 2);
             foreach ($chunks as $chunk){
                 if($chunkData = $this->getApiData($base, $chunk)){
                     $rates = array_merge($rates, $chunkData);
                 }
             }
        }else if(!$rates = $this->getApiData($base, $from)){
            return false;
        }

        $skip = is_array($from) && count($from) > 0;
        $result = [];
        foreach($rates as $code => $rate){
            list($baseCode, $rateCode) = explode('_', $code);
            if($skip && !in_array($rateCode, $from)){
                continue;
            }
            // 1 base to rate code
            $exchange = $rate;
            // 1 code to base
            if($reverse === true){
                $exchange = 1 / $exchange;
            }
            $result[$rateCode] = $this->formatResult($rateCode, 1, $exchange);
        }
        return $result;
    }



}