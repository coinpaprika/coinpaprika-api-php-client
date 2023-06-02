<?php

require_once __DIR__ . '/../vendor/autoload.php';

$client = new \Coinpaprika\Client();

// this will print historical tricks for given coin
// Free version only supports 1d interval
foreach ($client->getHistoricalTickerByCoinId('fake-coin', '2023-05-23', '1d') as $ticker) {
    echo sprintf(
        "Timestamp: %s, Price: %s, Volume24H: %s, MarketCap: %s\n",
        $ticker->getTimestamp(), $ticker->getPrice(), $ticker->getVolume24h(), $ticker->getMarketCap()
    );
}
