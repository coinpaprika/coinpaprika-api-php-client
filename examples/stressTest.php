<?php

/**
 * Stress test for the Coinpaprika API PHP client.
 *
 * Repeatedly exercises a rotating set of endpoints against the live API to
 * measure:
 *   - throughput (requests/sec)
 *   - per-call latency (min / p50 / p95 / p99 / max)
 *   - memory stability (should not grow unboundedly — surfaces leaks in the
 *     serializer cache or HTTP stack)
 *   - error-handling behavior under rate-limiting (429) and transient 5xx
 *
 * It does NOT try to maximize concurrency — it stays sequential so the
 * measurements reflect what a real single-process caller would see. To find
 * rate-limit ceilings lower `STRESS_DELAY_MS` to zero.
 *
 * Usage:
 *   php examples/stressTest.php
 *   STRESS_ITERATIONS=500 STRESS_DELAY_MS=50 php examples/stressTest.php
 *   API_KEY=xxx STRESS_ITERATIONS=1000 php examples/stressTest.php
 *
 * Env:
 *   STRESS_ITERATIONS   Total number of requests to issue    (default 100)
 *   STRESS_DELAY_MS     Sleep between calls in milliseconds  (default 100)
 *   STRESS_MAX_429      Stop after N 429 responses in a row  (default 5)
 *   API_KEY             Optional Pro API key (raises the free-tier rate limit)
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Coinpaprika\Client;
use Coinpaprika\Exception\InvalidResponseException;
use Coinpaprika\Exception\RateLimitExceededException;
use Coinpaprika\Exception\ResponseErrorException;

$iterations = max(1, (int) (getenv('STRESS_ITERATIONS') ?: 100));
$delayMs    = max(0, (int) (getenv('STRESS_DELAY_MS') ?: 100));
$max429     = max(1, (int) (getenv('STRESS_MAX_429') ?: 5));
$apiKey     = getenv('API_KEY') ?: null;

$client = new Client($apiKey);

/**
 * Calls to cycle through. Each tuple is [label, closure returning the result].
 * Keeping it diverse so the stress test hits:
 *   - typed-model happy paths that go through JMS (detects deserializer leaks)
 *   - responseRaw paths (different codepath)
 *   - path-only URLs and query-param URLs (both sendRequest branches)
 */
$calls = [
    ['getGlobalStats',       fn() => $client->getGlobalStats()],
    ['getTickerByCoinId',    fn() => $client->getTickerByCoinId('btc-bitcoin')],
    ['getTokenMeta',         fn() => $client->getTokenMeta('btc-bitcoin')],
    ['getCoins',             fn() => $client->getCoins()],
    ['getCoinMarkets',       fn() => $client->getCoinMarkets('btc-bitcoin')],
    ['search',               fn() => $client->search('bit', null, 3)],
    ['getExchanges',         fn() => $client->getExchanges()],
    ['getTags',              fn() => $client->getTags()],
    ['priceConverter',       fn() => $client->priceConverter([
        'base_currency_id'  => 'btc-bitcoin',
        'quote_currency_id' => 'usd-us-dollars',
        'amount'            => 1,
    ])],
    ['getTodayOHLCV',        fn() => $client->getTodayOHLCV('btc-bitcoin')],
];

$stats = [
    'total'        => 0,
    'ok'           => 0,
    'rate_limited' => 0,
    'errors'       => 0,
    'latencies_ms' => [],
    'by_endpoint'  => [],
];
$consecutive429 = 0;
$memStart = memory_get_usage(true);

echo "== Coinpaprika client stress test ==\n";
echo sprintf(
    "iterations=%d, delay_ms=%d, api_key=%s, endpoints=%d\n\n",
    $iterations, $delayMs, $apiKey ? 'present' : 'none', count($calls)
);

$startWall = microtime(true);

for ($i = 0; $i < $iterations; $i++) {
    [$label, $fn] = $calls[$i % count($calls)];
    $stats['by_endpoint'][$label] ??= ['ok' => 0, 'err' => 0, 'lat_ms' => []];

    $t0 = microtime(true);
    try {
        $fn();
        $elapsedMs = (microtime(true) - $t0) * 1000.0;
        $stats['ok']++;
        $stats['latencies_ms'][] = $elapsedMs;
        $stats['by_endpoint'][$label]['ok']++;
        $stats['by_endpoint'][$label]['lat_ms'][] = $elapsedMs;
        $consecutive429 = 0;
        $marker = '.';
    } catch (RateLimitExceededException $e) {
        $stats['rate_limited']++;
        $stats['by_endpoint'][$label]['err']++;
        $consecutive429++;
        $marker = 'R';
    } catch (ResponseErrorException | InvalidResponseException $e) {
        $stats['errors']++;
        $stats['by_endpoint'][$label]['err']++;
        $marker = 'E';
    } catch (\Throwable $e) {
        $stats['errors']++;
        $stats['by_endpoint'][$label]['err']++;
        $marker = '!';
        fwrite(STDERR, sprintf("\n[unexpected] %s on %s: %s\n", get_class($e), $label, $e->getMessage()));
    }
    $stats['total']++;

    echo $marker;
    if (($i + 1) % 50 === 0) {
        echo sprintf(" %d/%d\n", $i + 1, $iterations);
    }

    if ($consecutive429 >= $max429) {
        echo "\n[abort] $consecutive429 consecutive rate-limit responses; stopping early.\n";
        break;
    }

    if ($delayMs > 0) {
        usleep($delayMs * 1000);
    }
}

$wallSec = microtime(true) - $startWall;
$memEnd = memory_get_usage(true);
$memPeak = memory_get_peak_usage(true);

echo "\n\n== Summary ==\n";
echo sprintf("wall:                %.2fs\n", $wallSec);
echo sprintf("requests:            %d attempted, %d ok, %d rate_limited, %d errors\n",
    $stats['total'], $stats['ok'], $stats['rate_limited'], $stats['errors']);
echo sprintf("throughput:          %.2f req/s (including delays)\n", $stats['total'] / max($wallSec, 0.001));
echo sprintf("memory:              start=%s, end=%s, peak=%s, delta=%s\n",
    formatBytes($memStart), formatBytes($memEnd), formatBytes($memPeak),
    formatBytes($memEnd - $memStart));

if (!empty($stats['latencies_ms'])) {
    sort($stats['latencies_ms']);
    echo "\nlatency (ms, successful calls only):\n";
    echo sprintf("  min=%.1f  p50=%.1f  p95=%.1f  p99=%.1f  max=%.1f  mean=%.1f\n",
        $stats['latencies_ms'][0],
        percentile($stats['latencies_ms'], 0.50),
        percentile($stats['latencies_ms'], 0.95),
        percentile($stats['latencies_ms'], 0.99),
        end($stats['latencies_ms']),
        array_sum($stats['latencies_ms']) / count($stats['latencies_ms'])
    );
}

echo "\nper-endpoint:\n";
foreach ($stats['by_endpoint'] as $label => $row) {
    $meanMs = !empty($row['lat_ms']) ? array_sum($row['lat_ms']) / count($row['lat_ms']) : 0;
    echo sprintf("  %-22s  ok=%-4d  err=%-3d  mean=%.1fms\n", $label, $row['ok'], $row['err'], $meanMs);
}

echo "\nlegend: . = ok, R = rate-limited (429), E = error, ! = unexpected\n";
exit($stats['errors'] > 0 ? 1 : 0);


function percentile(array $sorted, float $p): float
{
    $n = count($sorted);
    if ($n === 0) return 0.0;
    $idx = (int) floor($p * ($n - 1));
    return $sorted[max(0, min($n - 1, $idx))];
}

function formatBytes(int $bytes): string
{
    $abs = abs($bytes);
    if ($abs >= 1 << 20) return sprintf('%.1fMB', $bytes / (1 << 20));
    if ($abs >= 1 << 10) return sprintf('%.1fKB', $bytes / (1 << 10));
    return $bytes . 'B';
}
