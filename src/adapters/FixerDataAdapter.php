<?php
/**
 * Created by PhpStorm.
 * User: kak
 * Date: 14.04.2017
 * Time: 19:09
 */

namespace kak\CurrencyConverter\adapters;


class FixerDataAdapter implements IAdapter
{
    public $url = 'http://api.fixer.io/latest?base=RUB&symbols=USD,GBP';

    public $client;


}