<?php

require_once __DIR__ . '/../vendor/autoload.php';

$client = new \Coinpaprika\Client();

// this will print list of icos
foreach ($client->getIcos() as $idx => $ico) {
    echo sprintf("Name: %s\n", $ico->getName());
}
