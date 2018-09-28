<?php

require_once __DIR__ . '/../vendor/autoload.php';

$client = new \Coinpaprika\Client();
$search = $client->search('v', null, 2);

// this prints people
foreach ($search->getPeople() as $person) {
    echo sprintf("Found person: %s <%s>\n", $person->getName(), $person->getId());
}

// this prints exchanges
foreach ($search->getExchanges() as $exchange) {
    echo sprintf("Found exchange: %s <%s>\n", $exchange->getName(), $exchange->getId());
}
