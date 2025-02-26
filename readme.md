kak\currency-converter from php lib
======================

Exchange rates/Currency Converter Library with features of caching and identifying currency from country code.

## Getting started
```php
<?php
require 'vendor/autoload.php';

use kak\CurrencyConverter\Converter;

$converter = new Converter;

var_dump($converter->get('USD', ['RUB', 'KZT']));

// caching currency
$cache = new Cache;
$converter = new Converter($cacheAdapter, [
  Converter::ADAPTER_OPEN_EXCHANGE_RATES => [
      'apiKey' => $_ENV['OPEN_EXCHANGE_RATES_KEY'] ?? '',
  ]
]);



echo "result 1 USD in RUB  \n";
var_dump($converter->get('USD', ['RUB'], 1, false, [Converter::ADAPTER_OPEN_EXCHANGE_RATES ]));
echo "result 2 RUB in USD  \n";
var_dump($converter->get('RUB', ['USD'], 2 , true, [Converter::ADAPTER_OPEN_EXCHANGE_RATES]));
var_dump($converter->getRates('RUB', [], false, [Converter::ADAPTER_OPEN_EXCHANGE_RATES]));

```

## CacheAdapter from Yii2
```php
<?php
namespace app\helpers\CacheAdapter;
use yii\base\Object;
use Yii;

class Cache Extends Object implements \kak\CurrencyConverter\ICache
{
    public function contains($id)
    {
       return Yii::$app->cache->get($id);
    }

    public function fetch($id)
    {
        return Yii::$app->cache->get($id);
    }

    public function delete($id)
    {
        return Yii::$app->cache->delete($id);
    }

    public function flushAll()
    {
        return Yii::$app->cache->flush();
    }

    public function save($id, $data, $lifeTime = 0)
    {
        Yii::$app->cache->set($id,$data,$lifeTime);
    }
} 
```

## Requirements
* PHP version 7.4 or later
* Curl Extension

## Installation
This library depends on composer for installation . For installation of composer, please visit [getcomposer.org](//getcomposer.org).
Add `"kak/currency-converter":"dev-master` to your composer.json and run `php composer.phar update`