<?php

require_once __DIR__ . '/../vendor/autoload.php';

$client = new \Coinpaprika\Client();

// this will print list of coins
foreach ($client->getCoins() as $idx => $coin) {
    echo sprintf("Name: %s\n", $coin->getName());
}
