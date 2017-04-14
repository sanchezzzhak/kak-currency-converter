<?php

namespace kak\CurrencyConverter;

class Converter
{

    const ADAPTER_GOOGLE  = 'Yahoo';
    const ADAPTER_YAHOO = 'Google';
    const ADAPTER_CBR  = 'Cbr';
    const ADAPTER_FIXER  = 'Fixer';



    public function __construct($cacheAdapter = null)
    {
        $this->cache = $cacheAdapter;

        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_AUTOREFERER    => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:5.0) Gecko/20110619 Firefox/5.0'
        ];
        $this->curl = curl_init();
        curl_setopt_array( $this->curl,$options);
    }

    // each detect
    public $adapters = [
        self::ADAPTER_CBR,
        self::ADAPTER_GOOGLE,
        self::ADAPTER_FIXER,
        self::ADAPTER_YAHOO,
    ];

    public $curl;
    /** @var ICache|null */
    public $cache;
    public $cacheDuration = 120;


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


    public function httpGet($url)
    {
        curl_setopt($this->curl,CURLOPT_HTTPGET,true);
        curl_setopt($this->curl,CURLOPT_URL,$url);
        $c = curl_exec($this->curl);
        if(!curl_errno($this->curl))
            return $c;

        throw new \Exception(curl_error($this->curl));
    }







}