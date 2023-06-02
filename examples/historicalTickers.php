<?php

require_once __DIR__ . '/../vendor/autoload.php';

$client = new \Coinpaprika\Client();

// this will print historical tricks for given coin
// valid interval values:
// Free: 24h
// Starter: 24h
// Pro: 24H
// Business: 1h, 6h, 12h, 24h
// Enterprise: 15m, 30m, 1h, 6h, 12h, 24h
foreach ($client->getHistoricalTickerByCoinId('fake-coin', '2023-05-23', '1d') as $ticker) {
    echo sprintf(
        "Timestamp: %s, Price: %s, Volume24H: %s, MarketCap: %s\n",
        $ticker->getTimestamp(), $ticker->getPrice(), $ticker->getVolume24h(), $ticker->getMarketCap()
    );
}
