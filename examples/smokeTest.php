<?php

/**
 * Smoke test for the Coinpaprika API PHP client.
 *
 * Runs a set of happy-path calls and deliberately bad calls against the LIVE
 * coinpaprika.com API to verify:
 *   1. URL construction (no trailing "?" on param-less calls).
 *   2. Query params go through Guzzle correctly.
 *   3. 4xx / 5xx are wrapped in ResponseErrorException / InvalidResponseException
 *      rather than crashing the app.
 *
 * Usage:
 *   php examples/smokeTest.php           # free API
 *   API_KEY=xxx php examples/smokeTest.php   # pro API
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Coinpaprika\Client;
use Coinpaprika\Exception\InvalidResponseException;
use Coinpaprika\Exception\RateLimitExceededException;
use Coinpaprika\Exception\ResponseErrorException;

$apiKey = getenv('API_KEY') ?: null;
$client = new Client($apiKey);

$pass = 0;
$fail = 0;

function run(string $label, callable $fn): void
{
    global $pass, $fail;
    try {
        $result = $fn();
        echo "  [OK]   $label\n";
        if (is_array($result) && !empty($result)) {
            $first = $result[0] ?? reset($result);
            if (is_object($first)) {
                echo "         returned: array<" . get_class($first) . "> (" . count($result) . " items)\n";
            } else {
                $preview = json_encode(array_slice($result, 0, 1), JSON_UNESCAPED_SLASHES);
                echo "         preview: " . substr($preview, 0, 160) . "\n";
            }
        } elseif (is_object($result)) {
            echo "         returned: " . get_class($result) . "\n";
        }
        $pass++;
    } catch (\Throwable $e) {
        echo "  [FAIL] $label\n";
        echo "         " . get_class($e) . ": " . $e->getMessage() . "\n";
        $fail++;
    }
}

function expectException(string $label, string $expected, callable $fn): void
{
    global $pass, $fail;
    try {
        $fn();
        echo "  [FAIL] $label (expected $expected, no exception)\n";
        $fail++;
    } catch (\Throwable $e) {
        if ($e instanceof $expected) {
            echo "  [OK]   $label -> " . get_class($e) . ": " . $e->getMessage() . "\n";
            $pass++;
        } else {
            echo "  [FAIL] $label (expected $expected, got " . get_class($e) . ": " . $e->getMessage() . ")\n";
            $fail++;
        }
    }
}

echo "== Coinpaprika client smoke test ==\n";
echo "API key: " . ($apiKey ? 'present (pro)' : 'none (free)') . "\n\n";

echo "-- Happy path: typed models --\n";
run('getGlobalStats',               fn() => $client->getGlobalStats());
run('getTickerByCoinId(btc)',       fn() => $client->getTickerByCoinId('btc-bitcoin'));
run('getTokenMeta(btc)',            fn() => $client->getTokenMeta('btc-bitcoin'));
run('getCoins (no params, no "?")', fn() => $client->getCoins());
run('search("bitcoin")',            fn() => $client->search('bitcoin', null, 3));

echo "\n-- Query params via Guzzle (historical endpoint) --\n";
if ($apiKey) {
    run(
        'getHistoricalTickerByCoinId(btc, 2024-01-01, 24h)',
        fn() => $client->getHistoricalTickerByCoinId('btc-bitcoin', '2024-01-01T00:00:00Z', '24h')
    );
} else {
    // Free plan cannot query historical ticker >1y back — but seeing the server
    // respond with a 402 means `start`/`interval` reached it correctly.
    expectException(
        'getHistoricalTickerByCoinId -> 402 plan gate (proves query params reach server)',
        ResponseErrorException::class,
        fn() => $client->getHistoricalTickerByCoinId('btc-bitcoin', '2024-01-01T00:00:00Z', '24h')
    );
}

echo "\n-- Happy path: new responseRaw endpoints --\n";
run('getCoinTwitter(btc)',       fn() => $client->getCoinTwitter('btc-bitcoin'));
run('getCoinEvents(btc)',        fn() => $client->getCoinEvents('btc-bitcoin'));
run('getCoinMarkets(btc)',       fn() => $client->getCoinMarkets('btc-bitcoin'));
run('getTodayOHLCV(btc)',        fn() => $client->getTodayOHLCV('btc-bitcoin'));
run('getExchanges()',            fn() => $client->getExchanges());
run('getPlatforms()',            fn() => $client->getPlatforms());
run('getTags()',                 fn() => $client->getTags());
run('priceConverter(btc->usd)',  fn() => $client->priceConverter([
    'base_currency_id'  => 'btc-bitcoin',
    'quote_currency_id' => 'usd-us-dollars',
    'amount'            => 1,
]));

echo "\n-- Error handling (crash-safety) --\n";
expectException(
    'unknown coin id -> ResponseErrorException (404)',
    ResponseErrorException::class,
    fn() => $client->getTickerByCoinId('this-coin-does-not-exist-xyz')
);
expectException(
    'unknown person id -> ResponseErrorException (404)',
    ResponseErrorException::class,
    fn() => $client->getPersonById('this-person-does-not-exist-xyz')
);

if (!$apiKey) {
    expectException(
        'pro-only endpoint without api key -> Exception (not a fatal)',
        \Throwable::class,
        fn() => $client->getKeyInfo()
    );
}

echo "\n== Result: $pass passed, $fail failed ==\n";
exit($fail === 0 ? 0 : 1);
