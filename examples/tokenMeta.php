<?php

require_once __DIR__ . '/../vendor/autoload.php';

$client = new \Coinpaprika\Client();

//Retrieve comprehensive descriptive information about tokens including
//their name, website, logo, and tags - all the data necessary for creating visually appealing coin pages.
$meta = $client->getTokenMeta('fake-token');

echo sprintf(
    "Name: %s, ID: %s, Type: %s, Logo: %s\n",
    $meta->getName(), $meta->getId(), $meta->getType(), $meta->getLogo()
);

foreach ($meta->getTags() as $tag) {
    echo sprintf(
        "Name: %s, ID: %s, CoinCounter: %s, IcoCounter: %s\n",
        $tag->getName(), $tag->getId(), $tag->getCoinCounter(), $tag->getIcoCounter()
    );
}

foreach ($meta->getTeam() as $member) {
    echo sprintf(
        "Name: %s, ID: %s, Position: %s\n",
        $member->getName(), $member->getId(), $member->getPosition()
    );
}