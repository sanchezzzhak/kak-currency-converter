<?php

namespace kak\CurrencyConverter\adapters;

interface IAdapter
{

    public function get($base , $from = [], $reverse = false);

    public function validateConfig(): bool;

}