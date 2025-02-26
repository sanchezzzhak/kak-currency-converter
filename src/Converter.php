<?php

namespace kak\CurrencyConverter;

use kak\CurrencyConverter\adapters\CbrDataAdapter;
use kak\CurrencyConverter\adapters\FreeCurrencyDataAdapter;
use kak\CurrencyConverter\adapters\GoogleDataAdapter;

/**
 * Class Converter
 * @package kak\CurrencyConverter
 */
class Converter
{
    public const ADAPTER_YAHOO = 'Yahoo';
    public const ADAPTER_CBR = 'Cbr';
    public const ADAPTER_FIXER = 'Fixer';
    public const ADAPTER_FREE_CURRENCY = 'FreeCurrency';
    public const ADAPTER_OPEN_EXCHANGE_RATES = 'OpenExchangeRates';

    /**
     * Converter constructor.
     * @param null $cacheAdapter
     * @param array $adaptersConfig
     */
    public function __construct($cacheAdapter = null, array $adaptersConfig = [])
    {
        $this->cache = $cacheAdapter;
        $this->adaptersConfig = $adaptersConfig;
        $this->httpClient = new http\HttpClient;
    }

    // each detect
    public array $adapters = [
        self::ADAPTER_CBR,
        // self::ADAPTER_FREE_CURRENCY,
        // self::ADAPTER_FORGE,
        // self::ADAPTER_YAHOO,
        self::ADAPTER_FIXER,
        self::ADAPTER_OPEN_EXCHANGE_RATES,
    ];

    // config adapters
    public array $adaptersConfig = [];
    public ?ICache $cache;
    public int $cacheDuration = 120;
    public http\HttpClient $httpClient;
    public bool $debug = false;

    /**
     * @param string $base
     * @param array $from
     * @param bool $reverse
     * @param array $priorityAdapters
     * @return array
     */
    private function getRatesDetectEach(
        string $base,
        array $from = [],
        bool $reverse = false,
        array $priorityAdapters = []
    ): array
    {
        $data = [];
        $originalFrom = $from;
        $skip = count($priorityAdapters);
        foreach ($this->adapters as $adapterName) {
            if ($skip && !in_array($adapterName, $priorityAdapters)) {
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

    /**
     * @param string $adapterName
     * @param string $base
     * @param array $from
     * @param bool $reverse
     * @return array|false
     */
    private function getRatesByAdapter(string $adapterName, string $base, array $from = [], bool $reverse = false)
    {
        $classPath = "\\kak\\CurrencyConverter\\adapters\\{$adapterName}DataAdapter";
        /** @var $adapter CbrDataAdapter|FreeCurrencyDataAdapter */
        $adapter = new $classPath([
            'client' => $this->httpClient,
            'debug' => $this->debug
        ]);

        $adapterOptions = $this->adaptersConfig[$adapterName] ?? [];
        foreach ($adapterOptions as $key => $option) {
            if (property_exists($adapter, $key)) {
                $adapter->{$key} = $option;
            } else {
                throw new \InvalidArgumentException(
                    sprintf('Adapter "%s" unknown set property "%s"', $adapterName, $key)
                );
            }
        }

        if (!$adapter->validateConfig()) {
            dd($adapter);
            return false;
        }

        return $adapter->get($base, $from, $reverse);
    }


    /**
     * @param $base string RUB
     * @param $from array [ 'KZT', 'IRR' ]
     * @param bool $reverse Get how much currency in base currency
     * @param array $priorityAdapters use only adapters
     * @return array
     */
    public function getRates(string $base, array $from, bool $reverse = false, array $priorityAdapters = []): array
    {
        return $this->getRatesDetectEach($base, $from, $reverse, $priorityAdapters);
    }


    /**
     * @param string $base
     * @param array $from
     * @param int $amount
     * @param bool|false $reverse
     * @param array $priorityAdapters
     * @return array|bool
     */
    public function get(
        string $base,
        array $from,
        int $amount = 1,
        bool $reverse = false,
        array $priorityAdapters = []
    )
    {
        $cacheId = 'CurrencyConverter::' . md5($base . '>' . implode(',', $from));
        $isCache = $this->cache !== null;
        if ($isCache && $rate = $this->cache->fetch($cacheId)) {
            return $rate;
        }

        if ($result = $this->getRatesDetectEach($base, $from, $reverse, $priorityAdapters)) {
            $data = [];
            foreach ($result as $code => $item) {
                $data[$code] = $amount * $item['value'];
            }
            if ($isCache && count($data)) {
                $this->cache->save($cacheId, $data, $this->cacheDuration);
            }
            return $data;
        }

        return false;
    }

}