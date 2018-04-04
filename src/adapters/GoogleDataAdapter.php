<?php
/**
 * Created by PhpStorm.
 * User: kak
 * Date: 14.04.2017
 * Time: 17:19
 */

namespace kak\CurrencyConverter\adapters;


/**
 * Class GoogleDataAdapter
 * @package kak\CurrencyConverter\adapters
 * @deprecated NOT USE NOT WORK PARSE ( NEW PARSER IN PROCESS )
 */
class GoogleDataAdapter extends BaseDataAdapter
{
    public $provider = 'google';

    public function get($base, $from = [], $reverse = false)
    {
        if($from === null || (is_array($from) && !count($from))) {
            return false;
        }
        $result = [];
        $from = is_string($from) ? [ $from ] : $from;
        foreach($from as $code){

            if (!$exchange = $this->parser($base,$code)) {
                continue;
            }
            if($reverse === false){
                $exchange = 1 / $exchange;
            }
            $result[$code] = $this->formatResult($code, 1, $exchange);
        }
        return $result;
    }

    public function parser($base, $from )
    {
        try {
            $url = 'https://finance.google.com/finance/converter?a=1&from=[fromCurrency]&to=[toCurrency]';
            $base = urlencode($base);
            $from = urlencode($from);
            $url = strtr($url,[
                '[fromCurrency]' => $from,
                '[toCurrency]'=> $base,
            ]);
            $rawdata = $this->client->get($url);
            $rawdata = explode("<span class=bld>",$rawdata);
            $rawdata = explode("</span>",$rawdata[1]);
            return preg_replace('/[^0-9\.]/i', null, $rawdata[0]);

        }catch (\Exception $e){}
        return false;
    }




}