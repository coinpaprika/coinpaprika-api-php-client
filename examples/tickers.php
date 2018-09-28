<?php

require_once __DIR__ . '/../vendor/autoload.php';

$client = new \Coinpaprika\Client();

// this will print 2 first tickers
foreach ($client->getTickers() as $idx => $ticker) {

    if ($idx >= 2) {
        break;
    }

    echo sprintf("Name: %s, Symbol: %s\n", $ticker->getName(), $ticker->getSymbol());
}
