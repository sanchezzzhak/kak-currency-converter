<?php

namespace kak\CurrencyConverter\http;

/**
 * Created by PhpStorm.
 * User: kak
 * Date: 16.04.2017
 * Time: 17:57
 */
class HttpClient
{

    public $curl;

    public function __construct()
    {
        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_AUTOREFERER => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:5.0) Gecko/20110619 Firefox/5.0'
        ];
        $this->curl = curl_init();
        curl_setopt_array($this->curl, $options);

    }

    public function get(string $url, array $headers = [])
    {
        curl_setopt($this->curl, CURLOPT_HTTPGET, true);
        curl_setopt($this->curl, CURLOPT_URL, $url);
        $c = curl_exec($this->curl);
        if (!curl_errno($this->curl)) {
            return $c;
        }

        throw new \Exception(curl_error($this->curl));
    }

}