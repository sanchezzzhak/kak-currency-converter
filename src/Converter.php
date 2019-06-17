<?php

namespace kak\CurrencyConverter;

use kak\CurrencyConverter\adapters\CbrDataAdapter;
use kak\CurrencyConverter\adapters\GoogleDataAdapter;

/**
 * Class Converter
 * @package kak\CurrencyConverter
 */
class Converter
{
    const ADAPTER_GOOGLE = 'Google';
    const ADAPTER_YAHOO = 'Yahoo';
    const ADAPTER_CBR = 'Cbr';
    const ADAPTER_FIXER = 'Fixer';
    //const ADAPTER_FORGE = 'Forge';
    const ADAPTER_FREE_CURRENCY = 'FreeCurrency';

    /**
     * Converter constructor.
     * @param null $cacheAdapter
     * @param array $adaptersConfig
     */
    public function __construct($cacheAdapter = null, $adaptersConfig = [])
    {
        $this->cache = $cacheAdapter;
        $this->adaptersConfig = $adaptersConfig;
        $this->httpClient = new http\HttpClient;
    }

    // each detect
    public $adapters = [
        self::ADAPTER_CBR,
        self::ADAPTER_FREE_CURRENCY,
        // self::ADAPTER_FORGE,
        // self::ADAPTER_YAHOO,
        // self::ADAPTER_GOOGLE,
        self::ADAPTER_FIXER,
    ];

    // config adapters
    public $adaptersConfig = [];


    public $curl;
    /** @var ICache|null */
    public $cache;
    public $cacheDuration = 120;

    public $httpClient;
    public $debug = false;



    private function getRatesDetectEach($base, $from = [], $reverse = false, $adapters = [])
    {
        $data = [];
        $originalFrom = $from;
        $skip = count($adapters);
        foreach ($this->adapters as $adapterName) {
            if ($skip && !in_array($adapterName, $adapters)) {
                continue;
            }
            if ($result = $this->getRatesByAdapter($adapterName, $base, $from, $reverse)) {
                foreach ($result as $code => $item) {
                    $data[$code] = $item;
                }

                if ($originalFrom !== null) {
                    $from = array_diff($originalFrom, array_keys($data));
                    if (!count($from)) {
                        break;
                    }
                }

            }
        }
        return $data;
    }

    private function getRatesByAdapter($adapterName, $base, $from = [], $reverse = false)
    {

        $classPath = "\\kak\\CurrencyConverter\\adapters\\{$adapterName}DataAdapter";
        /** @var  $adapter CbrDataAdapter|GoogleDataAdapter */
        $adapter = new $classPath([
            'client' => $this->httpClient,
            'debug' => $this->debug
        ]);

        $adapterOptions = isset($this->adaptersConfig[$adapterName]) ? $this->adaptersConfig[$adapterName] : [];
        foreach ($adapterOptions as $key => $option) {
            if (property_exists($adapter, $key)) {
                $adapter->{$key} = $option;
            } else {
                throw new \InvalidArgumentException(
                    sprintf('Adapter "%s" unknown set property "%s"', $adapterName, $key)
                );
            }
        }


        return $adapter->get($base, $from, $reverse);
    }


    /**
     * @param $base string RUB
     * @param $from array [ 'KZT', 'IRR' ]
     * @param bool|false $reverse Get how much currency in base currency
     * @param array $adapters set custom a priority adapters
     * @return array
     */
    public function getRates($base, $from, $reverse = false, $adapters = [])
    {
        return $this->getRatesDetectEach($base, $from, $reverse, $adapters);
    }


    /**
     * @param $base
     * @param $from
     * @param int $amount
     * @param bool|false $reverse
     * @param array $adapters
     * @return array|bool|int
     */
    public function get($base, $from, $amount = 1, $reverse = false, $adapters = [])
    {
        $cacheId = 'CurrencyConverter::' . md5($base . '>' . (is_array($from) ? implode(',', $from) : $from));
        $isCache = $this->cache !== null;
        if ($isCache && $rate = $this->cache->fetch($cacheId)) {
            return $rate;
        }
        $from = is_string($from) && $from !== null ? [$from] : $from;

        if ($result = $this->getRatesDetectEach($base, $from, $reverse, $adapters)) {
            $data = [];

            if (count($result) > 1) {
                foreach ($result as $code => $item) {
                    $data[$code] = $amount * $item['value'];
                }
                if ($isCache && count($data)) {
                    $this->cache->save($cacheId, $data, $this->cacheDuration);
                }
                return $data;
            }

            $data = isset($result[$from[0]]) ? ($amount * $result[$from[0]]['value']) : false;
            if ($isCache && $data) {
                $this->cache->save($cacheId, $data, $this->cacheDuration);
            }
            return $data;
        }

        return false;
    }

}