<?php
require __DIR__ .'/../vendor/autoload.php';

use kak\CurrencyConverter\Converter;

$converter = new Converter(null, [
    Converter::ADAPTER_FREE_CURRENCY => [
        'apiKey' => '{key}'
    ]
]);

$source= 'RUB';
$from = '';

if (php_sapi_name() === 'cli') {
    if (isset($argv[1])) {
        $source = $argv[1];
    }
    if (isset($argv[2])) {
        $from = $argv[2];
    }
} else {
    if (isset($_GET['from'])) {
        $from = $_GET['from'];
    }
    if (isset($_GET['source'])) {
        $source = $_GET['source'];
    }
}

$from = explode(",", $from);



//$from =  [
//    'USD' => 5,
//    'EUR' => 5,
//    'AZN' => 0,
//    'KZT' => 0,
//    'UAH' => 5,
//    'BYN' => 5,
//    'INR' => 5,
//    'PLN' => 5,
//    'IRR' => 3,
//    'TRY' => 5,
//    'MDL' => 5,
//    'UZS' => 0
//];
//
//
//
//



$result = $converter->get('RUB', $from, 1, false, [
    Converter::ADAPTER_FREE_CURRENCY
]);


var_dump($result);