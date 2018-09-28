<?php

namespace Coinpaprika\Tests;

use Coinpaprika\Client;
use Coinpaprika\Model\GlobalStats;

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
            $cacheDir,
            $this->getHttpClientMockWithResponse($expectedResponse)
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

    /**
     * @expectedException \Coinpaprika\Exception\ResponseErrorException
     * @expectedExceptionMessage id not found
     */
    public function testErrorResponse(): void
    {
        $expectedResponse = [
            'error' => 'id not found'
        ];

        $client = new Client(null, $this->getHttpClientMockWithResponse($expectedResponse, 404));
        $client->getTickerByCoinId('xxx');
    }

    /**
     * @expectedException \Coinpaprika\Exception\InvalidResponseException
     */
    public function testBadResponse(): void
    {
        $client = new Client(null, $this->getHttpClientMockWithResponse([], 444));
        $client->getTickerByCoinId('btc-bitcoin');
    }

    /**
     * @expectedException \Coinpaprika\Exception\RateLimitExceededException
     */
    public function testRateLimitExceeded(): void
    {
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
        $this->assertIco($search->getIcos()[1], $expectedResponse['icos'][1]);
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

    /**
     * @expectedException \Coinpaprika\Exception\ResponseErrorException
     */
    public function testSearchWithLimitError(): void
    {
        $client = new Client(null, $this->getHttpClientMockWithResponse([
            'error' => 'invalid parameters'
        ], 400));

        $client->search('t', null, 400);
    }
}
