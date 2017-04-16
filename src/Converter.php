<?php

namespace kak\CurrencyConverter;

use kak\CurrencyConverter\adapters\CbrDataAdapter;
use kak\CurrencyConverter\adapters\GoogleDataAdapter;

class Converter
{

    const ADAPTER_GOOGLE  = 'Google';
    const ADAPTER_YAHOO = 'Yahoo';
    const ADAPTER_CBR  = 'Cbr';
    const ADAPTER_FIXER  = 'Fixer';



    public function __construct($cacheAdapter = null)
    {
        $this->cache = $cacheAdapter;
        $this->httpClient = new http\httpClient;
    }

    // each detect
    public $adapters = [
        self::ADAPTER_CBR,
        self::ADAPTER_YAHOO,
        //self::ADAPTER_FIXER,
        //self::ADAPTER_GOOGLE
    ];

    public $curl;
    /** @var ICache|null */
    public $cache;
    public $cacheDuration = 120;

    public $httpClient;


    private function getRatesAll($base, $from = [] , $reverse = false)
    {


        foreach ($this->adapters as $adapterName){
            $classPath = "\\kak\\CurrencyConverter\\adapters\\{$adapterName}DataAdapter";
            var_dump($classPath);
            /** @var  $adapter CbrDataAdapter|GoogleDataAdapter*/
            $adapter = new $classPath([
                'client' => $this->httpClient
            ]);

            var_dump($adapter->get($base,$from, $reverse));
        }



    }




    public function getRates($base, $from = [], $reverse = false)
    {
        return $this->getRatesAll($base, $from, $reverse);

    }


    public function get($currencyTo,$currencyFrom, $amount = 1)
    {
        $cacheId = 'CurrencyConverter::'.$currencyTo.$currencyFrom;
        $isCache = $this->cache!==null;

        if($isCache && $rate = $this->cache->fetch($cacheId)){
            return $rate * $amount;
        }

        if(!$rate = $this->getDataProviderFromYahoo($currencyTo,$currencyFrom)){
            $rate = $this->getDataProviderFromGoogle($currencyTo,$currencyFrom);
        }

        if($isCache && $rate){
            $this->cache->save($cacheId,$rate,$this->cacheDuration);

        }
        return $rate * $amount;
    }


    /**
     * GET CBR RUSSION
     * @param $fromCurrency
     * @param $toCurrency
     * @return bool|float
     */
    public function getDataProviderFromCBR($fromCurrency, $toCurrency)
    {
        try {
            $yqlBaseUrl = "http://query.yahooapis.com/v1/public/yql";
            $yqlQuery = 'select * from yahoo.finance.xchange where pair in ("'.$fromCurrency.$toCurrency.'")';
            $yqlQueryUrl = $yqlBaseUrl . "?q=" . urlencode($yqlQuery);
            $yqlQueryUrl .= "&format=json&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys";
            $rawdata = $this->httpGet($yqlQueryUrl);
            $yqlJson =  json_decode($rawdata,true);
            return (float) 1*$yqlJson['query']['results']['rate']['Rate'];
        }catch (\Exception $e){
            return false;
        }
    }



    public function getDataProviderFromYahoo($fromCurrency, $toCurrency)
    {
        try {
            $yqlBaseUrl = "http://query.yahooapis.com/v1/public/yql";
            $yqlQuery = 'select * from yahoo.finance.xchange where pair in ("'.$fromCurrency.$toCurrency.'")';
            $yqlQueryUrl = $yqlBaseUrl . "?q=" . urlencode($yqlQuery);
            $yqlQueryUrl .= "&format=json&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys";
            $rawdata = $this->httpGet($yqlQueryUrl);
            $yqlJson =  json_decode($rawdata,true);
            return (float) 1*$yqlJson['query']['results']['rate']['Rate'];
        }catch (\Exception $e){
            return false;
        }
    }

    public function getDataProviderFromGoogle($fromCurrency, $toCurrency)
    {
        try {
            $url = 'https://www.google.com/finance/converter?a=1&from=[fromCurrency]&to=[toCurrency]';
            $fromCurrency = urlencode($fromCurrency);
            $toCurrency = urlencode($toCurrency);

            $url = strtr($url,[
                '[fromCurrency]' => $fromCurrency,
                '[toCurrency]'=> $toCurrency,
            ]);
            $rawdata = $this->httpGet($url);
            $rawdata = explode("<span class=bld>",$rawdata);
            $rawdata = explode("</span>",$rawdata[1]);
            return preg_replace('/[^0-9\.]/i', null, $rawdata[0]);
        }catch (\Exception $e){
            return false;
        }
    }










}