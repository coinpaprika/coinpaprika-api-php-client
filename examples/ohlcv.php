<?php

require_once __DIR__ . '/../vendor/autoload.php';

$client = new \Coinpaprika\Client();

// OHLCV data for technical analysis, identifying price trends and potential trading opportunities, can also be used for machine learning analysis to gain insights into the cryptocurrency market.
$data = $client->getOHLCV('fake-coin');
echo sprintf(
    "TimeOpen: %s, TimeClose: %s, Open: %s, Close: %s, High: %s, Low: %s, Volume: %s, MarketCap: %s\n",
    $data->getTimeOpen(), $data->getTimeClose(), $data->getOpen(), $data->getClose(), $data->getHigh(), $data->getLow(), $data->getVolume(), $data->getMarketCap()
);
