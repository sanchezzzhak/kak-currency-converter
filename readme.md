kak\currency-converter from php lib
======================

Exchange rates/Currency Converter Library with features of caching and identifying currency from country code.

## Getting started
```php
<?php
require 'vendor/autoload.php';

$converter = new kak\CurrencyConverter\Converter;
echo $converter->get('USD', 'RUB');

// caching currency
$cache = new app\helpers\CacheAdapter\Cache;
$converter = new kak\CurrencyConverter\Converter($cacheAdapter);

echo "result 1 usd in RUB  \n";
echo $converter->get('USD', 'RUB' , 1 , false ,[$converter::ADAPTER_YAHOO ]);
echo "result 2 RUB in USD  \n";
echo $converter->get('USD', 'RUB' , 2 , true ,[ $converter::ADAPTER_YAHOO ]);

// get all rates by RUB
// result array
echo $converter->getRates('RUB', null, false ,[ $converter::ADAPTER_YAHOO ]);



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
* PHP version 5.4 or later
* Curl Extension

## Installation
This library depends on composer for installation . For installation of composer, please visit [getcomposer.org](//getcomposer.org). 

Add `"kak/currency-converter":"dev-master` to your composer.json and run `php composer.phar update`