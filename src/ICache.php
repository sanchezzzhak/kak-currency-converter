<?php
namespace kak\CurrencyConverter;

interface ICache
{
    public function fetch($id);
    public function contains($id);
    public function save($id, $data, $lifeTime = 0);
    public function delete($id);
    public function flushAll();
}