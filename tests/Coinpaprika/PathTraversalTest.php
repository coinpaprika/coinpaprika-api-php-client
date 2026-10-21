<?php

namespace Coinpaprika\Tests;

use Coinpaprika\Client;

/**
 * Regression test for the id-encoding finding: every id-taking method
 * sprintf'd the caller's string straight into the request path without
 * rawurlencode. Guzzle's default curl handler removes dot segments and '#'
 * or '?' cut off the fixed suffix, so an id such as '../key/info' could
 * redirect the request to a different endpoint on the configured host under
 * the operator's key.
 *
 * Each payload below must either throw before any request is made, or
 * result in the request landing on the method's own fixed endpoint, never
 * on '/key/info' or '/usage'. The transport is a Guzzle MockHandler; no
 * network call is made.
 */
class PathTraversalTest extends AbstractTestCase
{
    public static function traversalPayloadsProvider(): array
    {
        return [
            ['../key/info'],
            ['../../usage'],
            ['..%2F..%2Fusage'],
            ['a/b'],
            ['x?y=1'],
            ['x#y'],
            ['%2e%2e'],
            ['..\\x'],
            ["\u{2024}\u{2024}"], // U+2024 ONE DOT LEADER
            ["\u{FF0E}\u{FF0E}"], // U+FF0E FULLWIDTH FULL STOP
            ["\u{2215}"],         // U+2215 DIVISION SLASH
            ["\u{FF0F}"],         // U+FF0F FULLWIDTH SOLIDUS
        ];
    }

    /**
     * method => [callable(Client, payload): mixed, expected fixed path prefix]
     */
    private function singleIdMethods(): array
    {
        return [
            'getTickerByCoinId' => [fn (Client $c, string $p) => $c->getTickerByCoinId($p), '/v1/tickers/'],
            'getTokenMeta' => [fn (Client $c, string $p) => $c->getTokenMeta($p), '/v1/coins/'],
            'getCoinTwitter' => [fn (Client $c, string $p) => $c->getCoinTwitter($p), '/v1/coins/'],
            'getCoinEvents' => [fn (Client $c, string $p) => $c->getCoinEvents($p), '/v1/coins/'],
            'getCoinExchanges' => [fn (Client $c, string $p) => $c->getCoinExchanges($p), '/v1/coins/'],
            'getCoinMarkets' => [fn (Client $c, string $p) => $c->getCoinMarkets($p), '/v1/coins/'],
            'getPersonById' => [fn (Client $c, string $p) => $c->getPersonById($p), '/v1/people/'],
            'getTagById' => [fn (Client $c, string $p) => $c->getTagById($p), '/v1/tags/'],
            'getExchangeById' => [fn (Client $c, string $p) => $c->getExchangeById($p), '/v1/exchanges/'],
            'getExchangeMarkets' => [fn (Client $c, string $p) => $c->getExchangeMarkets($p), '/v1/exchanges/'],
            'getContracts' => [fn (Client $c, string $p) => $c->getContracts($p), '/v1/contracts/'],
            'getOHLCV' => [fn (Client $c, string $p) => $c->getOHLCV($p, '1h'), '/v1/coins/'],
            'getHistoricalOHLCV' => [fn (Client $c, string $p) => $c->getHistoricalOHLCV($p, '2024-01-01'), '/v1/coins/'],
            'getTodayOHLCV' => [fn (Client $c, string $p) => $c->getTodayOHLCV($p), '/v1/coins/'],
        ];
    }

    /**
     * @dataProvider traversalPayloadsProvider
     */
    public function testSingleIdMethodsNeverEscapeTheirEndpoint(string $payload): void
    {
        foreach ($this->singleIdMethods() as $method => [$call, $prefix]) {
            [$http, $mock] = $this->makeGuzzleWithMock([[]]);
            $client = new Client(null, $http);

            try {
                $call($client, $payload);
            } catch (\Throwable $e) {
                // Rejecting the id outright is an acceptable, safe outcome.
                $this->assertInstanceOf(\Throwable::class, $e);
                continue;
            }

            $req = $mock->getLastRequest();
            $this->assertNotNull($req, "$method should have sent a request or thrown");
            $path = $req->getUri()->getPath();
            $this->assertStringStartsWith($prefix, $path, "$method with payload " . json_encode($payload));
            $this->assertStringNotContainsString('/key/info', $path, "$method with payload " . json_encode($payload));
            $this->assertStringNotContainsString('/usage', $path, "$method with payload " . json_encode($payload));
        }
    }

    /**
     * @dataProvider traversalPayloadsProvider
     */
    public function testGetTickerByContractNeverEscapesViaEitherId(string $payload): void
    {
        [$http1] = $this->makeGuzzleWithMock([[]]);
        $client1 = new Client(null, $http1);
        try {
            $client1->getTickerByContract($payload, 'eth-ethereum');
        } catch (\Throwable $e) {
        }

        [$http2, $mock2] = $this->makeGuzzleWithMock([[]]);
        $client2 = new Client(null, $http2);
        try {
            $client2->getTickerByContract('eth-ethereum', $payload);
        } catch (\Throwable $e) {
        }
        $req = $mock2->getLastRequest();
        if ($req !== null) {
            $path = $req->getUri()->getPath();
            $this->assertStringStartsWith('/v1/contracts/', $path);
            $this->assertStringNotContainsString('/key/info', $path);
        } else {
            $this->assertNull($req, 'rejecting the id outright is an acceptable, safe outcome');
        }
    }

    /**
     * @dataProvider traversalPayloadsProvider
     */
    public function testGetHistoricalByContractNeverEscapesViaEitherId(string $payload): void
    {
        [$http1] = $this->makeGuzzleWithMock([[]]);
        $client1 = new Client(null, $http1);
        try {
            $client1->getHistoricalByContract($payload, 'eth-ethereum');
        } catch (\Throwable $e) {
        }

        [$http2, $mock2] = $this->makeGuzzleWithMock([[]]);
        $client2 = new Client(null, $http2);
        try {
            $client2->getHistoricalByContract('eth-ethereum', $payload);
        } catch (\Throwable $e) {
        }
        $req = $mock2->getLastRequest();
        if ($req !== null) {
            $path = $req->getUri()->getPath();
            $this->assertStringStartsWith('/v1/contracts/', $path);
            $this->assertStringNotContainsString('/key/info', $path);
        } else {
            $this->assertNull($req, 'rejecting the id outright is an acceptable, safe outcome');
        }
    }

    public function testExactAuditPayloadsNeverReachKeyInfo(): void
    {
        $cases = [
            ['getTagById', ['../key/info']],
            ['getContracts', ['../key/info']],
            ['getCoinExchanges', ['../key/info#']],
        ];

        foreach ($cases as [$method, $args]) {
            [$http, $mock] = $this->makeGuzzleWithMock([[]]);
            $client = new Client(null, $http);

            try {
                $client->$method(...$args);
            } catch (\Throwable $e) {
                $this->assertInstanceOf(\Throwable::class, $e, "$method rejecting the id is an acceptable outcome");
                continue;
            }

            $req = $mock->getLastRequest();
            $this->assertStringNotContainsString('/key/info', $req->getUri()->getPath(), $method);
        }

        // getHistoricalByContract has its own fixed '/historical' suffix; the
        // audit's own payload leaves a trailing '?' which seg() now rejects.
        [$http, $mock] = $this->makeGuzzleWithMock([[]]);
        $client = new Client(null, $http);
        try {
            $client->getHistoricalByContract('eth-ethereum', '../../key/info?');
        } catch (\Throwable $e) {
        }
        $req = $mock->getLastRequest();
        if ($req !== null) {
            $this->assertStringNotContainsString('/key/info', $req->getUri()->getPath());
        } else {
            $this->assertNull($req, 'rejecting the id outright is an acceptable, safe outcome');
        }
    }

    public function testLegitimateIdsStillWork(): void
    {
        [$http, $mock] = $this->makeGuzzleWithMock([[]]);
        $client = new Client(null, $http);
        $client->getContracts('tags-x');

        $this->assertSame('/v1/contracts/tags-x', $mock->getLastRequest()->getUri()->getPath());
    }

    public function testRejectsEmptyDotAndDotDotIds(): void
    {
        [$http] = $this->makeGuzzleWithMock([[]]);
        $client = new Client(null, $http);

        foreach (['.', '..'] as $bad) {
            $threw = false;
            try {
                $client->getContracts($bad);
            } catch (\InvalidArgumentException $e) {
                $threw = true;
            }
            $this->assertTrue($threw, "getContracts('$bad') should throw");

            $threw = false;
            try {
                $client->getTagById($bad);
            } catch (\InvalidArgumentException $e) {
                $threw = true;
            }
            $this->assertTrue($threw, "getTagById('$bad') should throw");
        }
    }
}
