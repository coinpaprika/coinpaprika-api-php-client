<?php

namespace Coinpaprika\Tests;

use Coinpaprika\Client;
use Coinpaprika\Model\ChangelogEntry;
use Coinpaprika\Model\CoinMapping;
use Coinpaprika\Model\CoinMeta;
use Coinpaprika\Model\ExchangeDetail;
use Coinpaprika\Model\GlobalStats;
use Coinpaprika\Model\KeyInfo;
use Coinpaprika\Model\Market;
use GuzzleHttp\Psr7\Response;

/**
 * Class ClientTest
 *
 * @package \Coinpaprika\Tests
 *
 * @author Krzysztof Przybyszewski <kprzybyszewski@greywizard.com>
 */
class ClientTest extends AbstractTestCase
{
    public function testHasApiVersion(): void
    {
        $client = new Client();

        $this->assertEquals(
            'v1',
            $client->getApiVersion(),
            "Expected default version is v1."
        );
    }

    public function testGlobalStats(): void
    {
        $expectedResponse = [
            'market_cap_usd' => '208479597372',
            'volume_24h_usd' => '13405049041',
            'bitcoin_dominance_percentage' => '53.50605',
            'cryptocurrencies_number' => '1947',
            'last_updated' => '1537873242'
        ];

        $client = new Client(null, $this->getHttpClientMockWithResponse($expectedResponse));
        $stats = $client->getGlobalStats();

        $this->assertInstanceOf(GlobalStats::class, $stats);

        $this->assertEquals($expectedResponse['market_cap_usd'], $stats->getMarketCapUsd());
        $this->assertEquals($expectedResponse['volume_24h_usd'], $stats->getVolume24hUSD());
        $this->assertEquals($expectedResponse['bitcoin_dominance_percentage'], $stats->getBitcoinDominancePercentage());
        $this->assertEquals($expectedResponse['cryptocurrencies_number'], $stats->getCryptocurrenciesNumber());
        $this->assertEquals($expectedResponse['last_updated'], $stats->getLastUpdated());
    }

    public function testTickerByCoinIdWithMetadataCache(): void
    {
        $expectedResponse = $this->getTickerStructure();

        $cacheDir = __DIR__.'/../../var/cache/';
        $client = new Client(
            null,
            $this->getHttpClientMockWithResponse($expectedResponse),
            $cacheDir
        );

        $ticker = $client->getTickerByCoinId('my-coin');

        $this->assertTicker($ticker, $expectedResponse);

        $this->assertTrue(file_exists($cacheDir.'metadata/'));
    }

    public function testTickers(): void
    {
        $expectedResponse = [
            $this->getTickerStructure(),
            array_merge($this->getTickerStructure(), ['id' => 'test', 'name' => 'test'])
        ];

        $client = new Client(null, $this->getHttpClientMockWithResponse($expectedResponse));
        $tickers = $client->getTickers();

        $this->assertCount(2, $tickers);
        $this->assertTicker($tickers[0], $expectedResponse[0]);
        $this->assertTicker($tickers[1], $expectedResponse[1]);
    }

    public function testCoins(): void
    {
        $expectedResponse = [
            array_merge($this->getCoinStructure(), ['name' => 'xxx']),
            array_merge($this->getCoinStructure(), ['symbol' => 'BBB']),
            array_merge($this->getCoinStructure(), ['is_new' => false])
        ];

        $client = new Client(null, $this->getHttpClientMockWithResponse($expectedResponse));
        $coins = $client->getCoins();

        $this->assertCount(3, $coins);
        $this->assertCoin($coins[0], $expectedResponse[0]);
        $this->assertCoin($coins[1], $expectedResponse[1]);
        $this->assertCoin($coins[2], $expectedResponse[2]);
    }

    public function testIcos(): void
    {
        $expectedResponse = [
            array_merge($this->getIcoStructure(), ['name' => 'xxx']),
            array_merge($this->getIcoStructure(), ['symbol' => 'BBB']),
            array_merge($this->getIcoStructure(), ['received' => 10]),
            array_merge($this->getIcoStructure(), ['end_date' => '2018-10-22T00:00:00Z'])
        ];

        $client = new Client(null, $this->getHttpClientMockWithResponse($expectedResponse));

        $icos = $client->getIcos([
            'additional_fields' => 'tags_ids'
        ]);

        $this->assertCount(4, $icos);
        $this->assertIco($icos[0], $expectedResponse[0]);
        $this->assertIco($icos[1], $expectedResponse[1]);
        $this->assertIco($icos[2], $expectedResponse[2]);
        $this->assertIco($icos[3], $expectedResponse[3]);
    }

    public function testErrorResponse(): void
    {
        $expectedResponse = [
            'error' => 'id not found'
        ];

        $this->expectException(\Coinpaprika\Exception\ResponseErrorException::class);
        $this->expectExceptionMessage('id not found');

        $client = new Client(null, $this->getHttpClientMockWithResponse($expectedResponse, 404));
        $client->getTickerByCoinId('xxx');
    }

    public function testBadResponse(): void
    {
        $this->expectException(\Coinpaprika\Exception\InvalidResponseException::class);

        $client = new Client(null, $this->getHttpClientMockWithResponse([], 444));
        $client->getTickerByCoinId('btc-bitcoin');
    }

    public function testRateLimitExceeded(): void
    {
        $this->expectException(\Coinpaprika\Exception\RateLimitExceededException::class);

        $client = new Client(null, $this->getHttpClientMockWithResponse([], 429));
        $client->getTickerByCoinId('btc-bitcoin');
    }

    public function testSearchWithQuery(): void
    {
        $expectedResponse = $this->getSearchExpectedResponse();

        $client = new Client(null, $this->getHttpClientMockWithResponse($expectedResponse));

        $search = $client->search('bit');

        $this->assertCount(3, $search->getCurrencies());
        $this->assertCount(2, $search->getExchanges());
        $this->assertCount(2, $search->getIcos());
        $this->assertCount(2, $search->getPeople());
        $this->assertCount(1, $search->getTags());
        $this->assertCoin($search->getCurrencies()[1], $expectedResponse['currencies'][1]);
        $this->assertExchange($search->getExchanges()[0], $expectedResponse['exchanges'][0]);
        $this->assertPerson($search->getPeople()[0], $expectedResponse['people'][0]);
        $this->assertIcoSearch($search->getIcos()[1], $expectedResponse['icos'][1]);
        $this->assertTag($search->getTags()[0], $expectedResponse['tags'][0]);
    }

    public function testSearchWithQueryAndSingleCategory(): void
    {
        $categories = ['people'];
        $expectedResponse = ['people' => $this->getSearchExpectedResponse()['people']];

        $client = new Client(null, $this->getHttpClientMockWithResponse($expectedResponse));

        $search = $client->search('t', $categories);

        $this->assertCount(2, $search->getPeople());
        $this->assertCount(0, $search->getCurrencies());
    }

    public function testSearchWithQueryAndMultipleCategories(): void
    {
        $categories = ['people', 'tags'];
        $expectedResponse = [
            'people' => [$this->getSearchExpectedResponse()['people'][0]],
            'tags' => $this->getSearchExpectedResponse()['tags']
        ];

        $client = new Client(null, $this->getHttpClientMockWithResponse($expectedResponse));

        $search = $client->search('t', $categories);

        $this->assertCount(1, $search->getPeople());
        $this->assertCount(1, $search->getTags());
        $this->assertCount(0, $search->getIcos());
    }

    public function testSearchWithLimitError(): void
    {
        $this->expectException(\Coinpaprika\Exception\ResponseErrorException::class);

        $client = new Client(null, $this->getHttpClientMockWithResponse([
            'error' => 'invalid parameters'
        ], 400));

        $client->search('t', null, 400);
    }

    public function testGetCoinsProducesNoTrailingQuestionMark(): void
    {
        [$http, $mock] = $this->makeGuzzleWithMock([[]]);
        $client = new Client(null, $http);
        $client->getCoins();

        $uri = (string) $mock->getLastRequest()->getUri();
        $this->assertStringEndsWith('/v1/coins', $uri);
        $this->assertStringNotContainsString('?', $uri);
    }

    public function testHistoricalTickerPassesQueryParamsViaGuzzle(): void
    {
        [$http, $mock] = $this->makeGuzzleWithMock([[]]);
        $client = new Client(null, $http);
        $client->getHistoricalTickerByCoinId('btc-bitcoin', '2024-01-01', '24h');

        $req = $mock->getLastRequest();
        $this->assertSame('/v1/tickers/btc-bitcoin/historical', $req->getUri()->getPath());
        parse_str($req->getUri()->getQuery(), $query);
        $this->assertSame(['start' => '2024-01-01', 'interval' => '24h'], $query);
    }

    public function testApiKeyRoutesToProHostAndSetsAuthHeader(): void
    {
        [$http, $mock] = $this->makeGuzzleWithMock([[]]);
        $client = new Client('my-api-key', $http);
        $client->getCoins();

        $req = $mock->getLastRequest();
        $this->assertSame('api-pro.coinpaprika.com', $req->getUri()->getHost());
        $this->assertSame('my-api-key', $req->getHeaderLine('Authorization'));
    }

    public function testServerErrorIsWrappedInInvalidResponseException(): void
    {
        [$http] = $this->makeGuzzleWithMock([new Response(500, [], '<html>oops</html>')]);
        $client = new Client(null, $http);

        $this->expectException(\Coinpaprika\Exception\InvalidResponseException::class);
        $client->getCoins();
    }

    /**
     * Smoke test: every new v1.4.0 endpoint hits the expected URL and decodes a body.
     *
     * @dataProvider newEndpointsProvider
     */
    public function testNewEndpointUrls(string $method, array $args, string $expectedPath, array $expectedQuery): void
    {
        [$http, $mock] = $this->makeGuzzleWithMock([[]]);
        $client = new Client(null, $http);
        $result = $client->$method(...$args);

        $this->assertIsArray($result, "$method should return an array");
        $req = $mock->getLastRequest();
        $this->assertSame('GET', $req->getMethod(), $method);
        $this->assertSame($expectedPath, $req->getUri()->getPath(), $method);

        parse_str($req->getUri()->getQuery(), $actualQuery);
        $this->assertEquals($expectedQuery, $actualQuery, $method);
    }

    public function testGetTickersHitsPluralEndpoint(): void
    {
        [$http, $mock] = $this->makeGuzzleWithMock([[]]);
        $client = new Client(null, $http);
        $client->getTickers();

        $this->assertSame('/v1/tickers', $mock->getLastRequest()->getUri()->getPath());
    }

    public function testGetTickerByCoinIdHitsPluralEndpoint(): void
    {
        [$http, $mock] = $this->makeGuzzleWithMock([$this->getTickerStructure()]);
        $client = new Client(null, $http);
        $client->getTickerByCoinId('btc-bitcoin');

        $this->assertSame('/v1/tickers/btc-bitcoin', $mock->getLastRequest()->getUri()->getPath());
    }

    public function testUserAgentIncludesVersion(): void
    {
        [$http, $mock] = $this->makeGuzzleWithMock([[]]);
        $client = new Client(null, $http);
        $client->getCoins();

        $this->assertSame(
            sprintf('Coinpaprika API Client - PHP/%s', Client::VERSION),
            $mock->getLastRequest()->getHeaderLine('User-Agent')
        );
    }

    public function testGetTokenMetaDeserializes(): void
    {
        $fixture = [
            'id' => 'btc-bitcoin',
            'name' => 'Bitcoin',
            'symbol' => 'BTC',
            'rank' => 1,
            'is_new' => false,
            'is_active' => true,
            'type' => 'coin',
            'logo' => 'https://static.coinpaprika.com/coin/btc-bitcoin/logo.png',
            'tags' => [],
            'team' => [],
            'description' => 'Bitcoin is a decentralized...',
            'message' => '',
            'open_source' => true,
            'started_at' => '2009-01-03T00:00:00Z',
            'development_status' => 'Working product',
            'hardware_wallet' => true,
            'proof_type' => 'Proof of Work',
            'org_structure' => 'Decentralized',
            'hash_algorithm' => 'SHA256',
            'first_data_at' => '2010-07-17T00:00:00Z',
            'last_data_at' => '2026-04-12T00:00:00Z',
        ];

        [$http] = $this->makeGuzzleWithMock([$fixture]);
        $meta = (new Client(null, $http))->getTokenMeta('btc-bitcoin');

        $this->assertInstanceOf(CoinMeta::class, $meta);
        $this->assertSame('btc-bitcoin', $meta->getId());
        $this->assertSame('BTC', $meta->getSymbol());
        $this->assertSame(1, $meta->getRank());
        $this->assertTrue($meta->isActive());
        $this->assertFalse($meta->isNew());
        $this->assertSame('Proof of Work', $meta->getProofType());
        $this->assertSame('SHA256', $meta->getHashAlgorithm());
    }

    public function testGetExchangesDeserializes(): void
    {
        $fixture = [
            [
                'id' => 'binance',
                'name' => 'Binance',
                'type' => ['cex', 'spot'],
                'description' => 'desc',
                'active' => true,
                'website_status' => true,
                'api_status' => true,
                'message' => null,
                'links' => ['twitter' => ['https://twitter.com/binance']],
                'fiats' => [['name' => 'Euro', 'symbol' => 'EUR']],
                'markets_data_fetched' => true,
                'adjusted_rank' => 1,
                'reported_rank' => 4,
                'currencies' => 439,
                'markets' => 1379,
                'quotes' => ['USD' => ['reported_volume_24h' => 1.0]],
                'last_updated' => '2026-04-12T21:34:43Z',
                'sessions_per_month' => 86401227,
                'confidence_score' => 0.574832,
            ],
        ];

        [$http] = $this->makeGuzzleWithMock([$fixture]);
        $exchanges = (new Client(null, $http))->getExchanges();

        $this->assertCount(1, $exchanges);
        $this->assertInstanceOf(ExchangeDetail::class, $exchanges[0]);
        $this->assertSame('binance', $exchanges[0]->getId());
        $this->assertSame('Binance', $exchanges[0]->getName());
        $this->assertSame(['cex', 'spot'], $exchanges[0]->getType());
        $this->assertSame(1, $exchanges[0]->getAdjustedRank());
        $this->assertSame(4, $exchanges[0]->getReportedRank());
        $this->assertSame(439, $exchanges[0]->getCurrencies());
        $this->assertEqualsWithDelta(0.574832, $exchanges[0]->getConfidenceScore(), 1e-6);
        $this->assertTrue($exchanges[0]->isActive());
    }

    public function testGetExchangeByIdDeserializes(): void
    {
        $fixture = [
            'id' => 'binance',
            'name' => 'Binance',
            'adjusted_rank' => 1,
            'confidence_score' => 0.9,
        ];

        [$http, $mock] = $this->makeGuzzleWithMock([$fixture]);
        $exchange = (new Client(null, $http))->getExchangeById('binance');

        $this->assertInstanceOf(ExchangeDetail::class, $exchange);
        $this->assertSame('binance', $exchange->getId());
        $this->assertSame(1, $exchange->getAdjustedRank());
        $this->assertSame('/v1/exchanges/binance', $mock->getLastRequest()->getUri()->getPath());
    }

    public function testGetCoinMarketsDeserializes(): void
    {
        $fixture = [
            [
                'exchange_id' => 'binance',
                'exchange_name' => 'Binance',
                'pair' => 'BTC/USDT',
                'base_currency_id' => 'btc-bitcoin',
                'base_currency_name' => 'bitcoin',
                'quote_currency_id' => 'usdt-tether',
                'quote_currency_name' => 'tether',
                'market_url' => 'https://www.binance.com/en/trade/BTC_USDT',
                'category' => 'Spot',
                'fee_type' => 'Percentage',
                'outlier' => false,
                'adjusted_volume_24h_share' => 4.13,
                'quotes' => ['USD' => ['price' => 71000.0, 'volume_24h' => 1.0]],
                'trust_score' => 'high',
                'last_updated' => '2026-04-12T21:33:54Z',
            ],
        ];

        [$http] = $this->makeGuzzleWithMock([$fixture]);
        $markets = (new Client(null, $http))->getCoinMarkets('btc-bitcoin');

        $this->assertCount(1, $markets);
        $this->assertInstanceOf(Market::class, $markets[0]);
        $this->assertSame('binance', $markets[0]->getExchangeId());
        $this->assertSame('BTC/USDT', $markets[0]->getPair());
        $this->assertSame('btc-bitcoin', $markets[0]->getBaseCurrencyId());
        $this->assertSame('Spot', $markets[0]->getCategory());
        $this->assertSame('high', $markets[0]->getTrustScore());
        $this->assertFalse($markets[0]->isOutlier());
        $this->assertEqualsWithDelta(4.13, $markets[0]->getAdjustedVolume24hShare(), 1e-6);
        $this->assertArrayHasKey('USD', $markets[0]->getQuotes());
    }

    public function testGetKeyInfoDeserializes(): void
    {
        // Fixture taken from Coinpaprika docs example:
        // https://docs.coinpaprika.com/api-reference/key/get-api-key-info
        $fixture = [
            'plan' => 'pro',
            'plan_started_at' => '2022-09-16T10:17:24Z',
            'plan_status' => 'active',
            'portal_url' => 'https://coinpaprika.com/api/panel',
            'usage' => [
                'message' => 'limited plan',
                'current_month' => [
                    'requests_made' => 18,
                    'requests_left' => 499982,
                ],
            ],
        ];

        [$http, $mock] = $this->makeGuzzleWithMock([$fixture]);
        $info = (new Client('fake-key', $http))->getKeyInfo();

        $this->assertInstanceOf(KeyInfo::class, $info);
        $this->assertSame('pro', $info->getPlan());
        $this->assertSame('active', $info->getPlanStatus());
        $this->assertSame('https://coinpaprika.com/api/panel', $info->getPortalUrl());
        $this->assertSame('limited plan', $info->getUsage()->getMessage());
        $this->assertSame(18, $info->getUsage()->getCurrentMonth()->getRequestsMade());
        $this->assertSame(499982, $info->getUsage()->getCurrentMonth()->getRequestsLeft());
        $this->assertSame('api-pro.coinpaprika.com', $mock->getLastRequest()->getUri()->getHost());
        $this->assertSame('fake-key', $mock->getLastRequest()->getHeaderLine('Authorization'));
    }

    public function testGetKeyInfoHandlesUnlimitedPlanSentinel(): void
    {
        $fixture = [
            'plan' => 'enterprise',
            'plan_started_at' => '2024-01-01T00:00:00Z',
            'plan_status' => 'active',
            'portal_url' => 'https://coinpaprika.com/api/panel',
            'usage' => [
                'message' => 'unlimited plan',
                'current_month' => ['requests_made' => -1, 'requests_left' => -1],
            ],
        ];

        [$http] = $this->makeGuzzleWithMock([$fixture]);
        $info = (new Client('fake-key', $http))->getKeyInfo();

        $this->assertSame(-1, $info->getUsage()->getCurrentMonth()->getRequestsMade());
        $this->assertSame(-1, $info->getUsage()->getCurrentMonth()->getRequestsLeft());
    }

    public function testGetCoinsMappingsDeserializes(): void
    {
        // Fixture taken from Coinpaprika docs example:
        // https://docs.coinpaprika.com/api-reference/coins/api-id-mappings
        $fixture = [
            [
                'coinpaprika' => 'btc-bitcoin',
                'coinmarketcap' => '1',
                'coingecko' => 'bitcoin',
                'cryptocompare' => '1',
                'isin' => 'XTV15WLZJMF0',
                'dti' => 'V15WLZJMF',
                'updated_at' => '2024-12-12T13:13:28Z',
            ],
        ];

        [$http] = $this->makeGuzzleWithMock([$fixture]);
        $mappings = (new Client('fake-key', $http))->getCoinsMappings();

        $this->assertCount(1, $mappings);
        $this->assertInstanceOf(CoinMapping::class, $mappings[0]);
        $this->assertSame('btc-bitcoin', $mappings[0]->getCoinpaprika());
        $this->assertSame('1', $mappings[0]->getCoinmarketcap());
        $this->assertSame('bitcoin', $mappings[0]->getCoingecko());
        $this->assertSame('1', $mappings[0]->getCryptocompare());
        $this->assertSame('XTV15WLZJMF0', $mappings[0]->getIsin());
        $this->assertSame('V15WLZJMF', $mappings[0]->getDti());
        $this->assertSame('2024-12-12T13:13:28Z', $mappings[0]->getUpdatedAt());
    }

    public function testGetChangelogIdsDeserializes(): void
    {
        // Fixture taken from Coinpaprika docs example:
        // https://docs.coinpaprika.com/api-reference/changelog/get-id-changelog-for-all-coins
        $fixture = [
            [
                'currency_id' => 'btc-bitcoin',
                'old_id' => 'btc-bitcoin123',
                'new_id' => 'btc-bitcoin456',
                'changed_at' => '2022-07-15T07:00:00Z',
            ],
        ];

        [$http, $mock] = $this->makeGuzzleWithMock([$fixture]);
        $entries = (new Client('fake-key', $http))->getChangelogIds();

        $this->assertCount(1, $entries);
        $this->assertInstanceOf(ChangelogEntry::class, $entries[0]);
        $this->assertSame('btc-bitcoin', $entries[0]->getCurrencyId());
        $this->assertSame('btc-bitcoin123', $entries[0]->getOldId());
        $this->assertSame('btc-bitcoin456', $entries[0]->getNewId());
        $this->assertSame('2022-07-15T07:00:00Z', $entries[0]->getChangedAt());

        parse_str($mock->getLastRequest()->getUri()->getQuery(), $query);
        $this->assertSame([], $query, 'changelog/ids should not add query params when none are given');
    }

    public function testGetChangelogIdsSupportsPagination(): void
    {
        [$http, $mock] = $this->makeGuzzleWithMock([[]]);
        (new Client('fake-key', $http))->getChangelogIds(['page' => 3]);

        parse_str($mock->getLastRequest()->getUri()->getQuery(), $query);
        $this->assertSame(['page' => '3'], $query);
    }

    public function newEndpointsProvider(): array
    {
        return [
            'getCoinTwitter'         => ['getCoinTwitter',         ['btc-bitcoin'],                         '/v1/coins/btc-bitcoin/twitter',         []],
            'getCoinEvents'          => ['getCoinEvents',          ['btc-bitcoin'],                         '/v1/coins/btc-bitcoin/events',          []],
            'getCoinExchanges'       => ['getCoinExchanges',       ['btc-bitcoin'],                         '/v1/coins/btc-bitcoin/exchanges',       []],
            'getCoinMarkets'         => ['getCoinMarkets',         ['btc-bitcoin', ['quotes' => 'USD']],    '/v1/coins/btc-bitcoin/markets',         ['quotes' => 'USD']],
            'getCoinMarkets-noopts'  => ['getCoinMarkets',         ['btc-bitcoin'],                         '/v1/coins/btc-bitcoin/markets',         []],
            'getHistoricalOHLCV'     => ['getHistoricalOHLCV',     ['btc-bitcoin', '2024-01-01', ['limit' => 10]], '/v1/coins/btc-bitcoin/ohlcv/historical', ['start' => '2024-01-01', 'limit' => '10']],
            'getTodayOHLCV'          => ['getTodayOHLCV',          ['btc-bitcoin'],                         '/v1/coins/btc-bitcoin/ohlcv/today',     []],
            'getCoinsMappings'       => ['getCoinsMappings',       [],                                      '/v1/coins/mappings',                    []],
            'getPersonById'          => ['getPersonById',          ['vitalik-buterin'],                     '/v1/people/vitalik-buterin',            []],
            'getTags'                => ['getTags',                [['additional_fields' => 'coins']],      '/v1/tags',                              ['additional_fields' => 'coins']],
            'getTagById'             => ['getTagById',             ['defi'],                                '/v1/tags/defi',                         []],
            'getExchanges'           => ['getExchanges',           [],                                      '/v1/exchanges',                         []],
            // getExchangeById is deserialised into a single ExchangeDetail object
            // now (not an array) — URL-smoke covered by testGetExchangeByIdDeserializes.
            'getExchangeMarkets'     => ['getExchangeMarkets',     ['binance'],                             '/v1/exchanges/binance/markets',         []],
            'priceConverter'         => ['priceConverter',         [['base_currency_id' => 'btc-bitcoin', 'quote_currency_id' => 'usd-us-dollars', 'amount' => 1]], '/v1/price-converter', ['base_currency_id' => 'btc-bitcoin', 'quote_currency_id' => 'usd-us-dollars', 'amount' => '1']],
            'getPlatforms'           => ['getPlatforms',           [],                                      '/v1/contracts',                         []],
            'getContracts'           => ['getContracts',           ['eth-ethereum'],                        '/v1/contracts/eth-ethereum',            []],
            'getTickerByContract'    => ['getTickerByContract',    ['eth-ethereum', '0xdac17'],             '/v1/contracts/eth-ethereum/0xdac17',    []],
            'getHistoricalByContract'=> ['getHistoricalByContract',['eth-ethereum', '0xdac17', ['start' => '2024-01-01']], '/v1/contracts/eth-ethereum/0xdac17/historical', ['start' => '2024-01-01']],
            // getKeyInfo is deserialised into a single KeyInfo object (not an
            // array); URL-smoke covered by testGetKeyInfoDeserializes.
            'getChangelogIds'        => ['getChangelogIds',        [],                                      '/v1/changelog/ids',                     []],
        ];
    }
}
