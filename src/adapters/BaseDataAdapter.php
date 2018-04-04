<?php
namespace kak\CurrencyConverter\adapters;



class BaseDataAdapter implements IAdapter
{
    /**
     * @var \kak\CurrencyConverter\http\HttpClient;
     */
    public $client;
    public $provider = '';

    public function __construct($config = [])
    {
        foreach ($config as $key => $item) {
            $this->{$key} = $item;
        }
    }

    protected function strToFloat($str){

        return strpos($str, ',') !== false ? str_replace(',', '.', $str) : $str;
    }

    public function get($base, $from = [], $reverse = false)
    {
        // TODO: Implement get() method.
    }


    public function correction($exch, $percent = 0)
    {
        return ($exch-($percent / 100 * $exch));
    }


    public function formatResult($code, $nominal, $value)
    {
        return [
            'currency' => $code,
            'nominal' => $nominal,
            'provider' => $this->provider,
            'value' => $value
        ];
    }

    /**
     * @param $url
     * @param array $data
     * @return string
     */
    public function buildUrl($url, $data = array()){
        $parsed = parse_url($url);
        isset($parsed['query']) ? parse_str($parsed['query'], $parsed['query']) : $parsed['query'] = [];
        $params = isset($parsed['query']) ? array_merge($parsed['query'], $data) : $data;
        $parsed['query'] = ($params) ? '?' . http_build_query($params) : '';
        if (!isset($parsed['path']))
            $parsed['path'] = '/';

        $scheme = isset($parsed['scheme']) ? $parsed['scheme']: 'http';
        $host   = isset($parsed['host']) ? $scheme. '://' . $parsed['host'] . (!empty($parsed['port']) ? ':'.$parsed['port']:'') : '';
        return  $host . $parsed['path'] . $parsed['query'];
    }


}