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

        $rates = [
            'AZNUSD' => $converter->get('USD', 'AZN'),
            'AZNEUR' => $converter->get('EUR', 'AZN'),
            'AZNRUB' => $converter->get('RUB', 'AZN'),

            'KZTUSD' => $converter->get('USD', 'KZT'),
            'KZTEUR' => $converter->get('EUR', 'KZT'),
            'KZTRUB' => $converter->get('RUB', 'KZT'),
        ];

        var_dump($rates);
    }
}