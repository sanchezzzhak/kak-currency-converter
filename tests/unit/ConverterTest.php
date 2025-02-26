<?php

use Codeception\Test\Unit;
use kak\CurrencyConverter\Converter;

class ConverterTest extends Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    public function testCbrAdapter(): void
    {
        $converter = new Converter();
        $converter->adapters = [
            Converter::ADAPTER_CBR,
        ];
        $from =  [
            'USD' => 5,
            'EUR' => 5,
            'AZN' => 0,
            'KZT' => 0,
            'UAH' => 5,
            'BYN' => 5,
            'INR' => 5,
            'PLN' => 5,
            'IRR' => 3,
            'TRY' => 5,
            'MDL' => 5,
            'UZS' => 0
        ];
        $result = $converter->get('RUB', array_keys($from));
        $this->assertNotFalse($result);
        $this->assertNotNull($result);
        dump($result);
    }

    public function testOpenExchangeRates(): void
    {
        $apiKey =  $_ENV['OPEN_EXCHANGE_RATES_KEY'] ?? '';
        $this->assertNotEmpty($apiKey, 'Env api key is empty set ENV[OPEN_EXCHANGE_RATES_KEY]');

        $converter = new Converter(null, [
            Converter::ADAPTER_OPEN_EXCHANGE_RATES => [
                'apiKey' => $apiKey,
            ]
        ]);
        $converter->adapters = [
            Converter::ADAPTER_OPEN_EXCHANGE_RATES,
        ];
        $from =  [
            'USD' => 5,
            'EUR' => 5,
            'AZN' => 0,
            'KZT' => 0,
            'UAH' => 5,
            'BYN' => 5,
            'INR' => 5,
            'PLN' => 5,
            'IRR' => 3,
            'TRY' => 5,
            'MDL' => 5,
            'UZS' => 0
        ];
        $result = $converter->get('RUB', array_keys($from));
        $this->assertNotFalse($result);
        $this->assertNotNull($result);
        dump($result);
    }

}