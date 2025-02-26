<?php
/**
 * Created by PhpStorm.
 * User: kak
 * Date: 14.04.2017
 * Time: 17:21
 */

namespace kak\CurrencyConverter\adapters;

/**
 * Get rates for CentralBankRussian
 */
class CbrDataAdapter extends BaseDataAdapter
{
    public $provider = 'Cbr';

    public $apiUrl = 'http://www.cbr.ru/scripts/XML_daily.asp';

    public function validateConfig(): bool
    {
        return true;
    }

    public function get($base, $from = [], $reverse = false)
    {
        if ($base !== 'RUB') {
            return false;
        }
        try {
            $url = $this->buildUrl($this->apiUrl, [
                'date_req' => date('d.m.Y', time() + 86400)
            ]);
            $currencyXml = $this->client->get($url);
        } catch (\Exception $e) {
            return false;
        }

        $currencyXml = simplexml_load_string($currencyXml);
        $result = [];
        $skip = is_array($from) && count($from) > 0;
        foreach ($currencyXml->Valute as $currency) {
            $code = (string)$currency->CharCode;

            if ($skip && !in_array($code, $from)) {
                continue;
            }
            $par = (int)$currency->Nominal;
            $exchange = $this->asToFloat($currency->Value);
            // валюта в = рблей
            if ($par > 1) {
                $exchange = $exchange / $par;
                $par = 1;
            }
            // 1 российский рубль = в валюте
            if ($reverse === false) {
                $exchange = 1 / $exchange;
            }

            $exchange = round($this->correction($exchange, 0), 5);
            $result[$code] = $this->formatResult($code, $par, $exchange);
        }

        return $result;
    }


}