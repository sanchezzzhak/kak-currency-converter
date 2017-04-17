<?php
/**
 * Created by PhpStorm.
 * User: kak
 * Date: 14.04.2017
 * Time: 19:09
 */

namespace kak\CurrencyConverter\adapters;

class FixerDataAdapter extends BaseDataAdapter
{
    public $provider = 'fixer';

    public function get($base, $from = [], $reverse = false)
    {
        $url = 'http://api.fixer.io/latest?base=' . $base;
        try {
            $jsonData = json_decode($this->client->get($url),true);
        } catch (\Exception  $e) {
            return false;
        }
        $result = [];

        $skip = is_array($from) && count($from) > 0 ;
        foreach($jsonData['rates'] as $code => $rate){
            if($skip && !in_array($code,$from)){
                continue;
            }
            // 1 base to rate code
            $exchange = $rate;
            // 1 code to base
            if($reverse === true){
                $exchange = 1 / $exchange;
            }
            $result[$code] = $this->formatResult($code,1,$exchange);
        }
        return $result;
    }



}