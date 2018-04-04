<?php



class ConverterTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before()
    {
    }

    protected function _after()
    {
    }

    // tests
    public function testMe()
    {
        $converter = new \kak\CurrencyConverter\Converter(null);


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




        var_dump($converter->get('RUB', array_keys($from)));
    }
}