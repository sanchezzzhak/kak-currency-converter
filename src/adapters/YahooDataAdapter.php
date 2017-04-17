<?php
/**
 * Created by PhpStorm.
 * User: kak
 * Date: 14.04.2017
 * Time: 17:21
 */

namespace kak\CurrencyConverter\adapters;

class YahooDataAdapter extends BaseDataAdapter
{
    public $provider = 'Yahoo';

    public function get( $base, $from = [] , $reverse = false)
    {
        if(!count($from)) {
            return false;
        }

        $yqlBaseUrl = "http://query.yahooapis.com/v1/public/yql";

        $queryData = [];
        foreach ($from as $item){
            $queryData[] = $reverse === false ? $base . $item : $item . $base;
        }

        $yqlQuery = 'select * from yahoo.finance.xchange where pair in ("'. implode(',',$queryData).'")';
        $yqlQueryUrl = $yqlBaseUrl . "?q=" . urlencode($yqlQuery);
        $yqlQueryUrl .= "&q=1&format=json&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys";
        $rawdata = $this->client->get($yqlQueryUrl);
        $yqlJson =  json_decode($rawdata,true);
        $result = [];
        $arrayData = $yqlJson['query']['count'] == 1 ? $yqlJson['query']['results'] : $yqlJson['query']['results']['rate'];
        foreach ($arrayData as $data) {
            $part = explode('/',$data['Name']);
            $code = !$reverse ? $part[1] : $part[0];
            $result[$code] = $this->formatResult($code,1,floatval($data['Rate']));
        }
        return $result;
    }



}