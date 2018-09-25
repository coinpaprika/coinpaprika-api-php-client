<?php

namespace Coinpaprika\Tests;

use Coinpaprika\Client;
use Coinpaprika\Model\Coin;
use Coinpaprika\Model\GlobalStats;
use Coinpaprika\Model\Ticker;
use PHPUnit\Framework\MockObject\MockObject;
use \PHPUnit\Framework\TestCase;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class ClientTest
 *
 * @package \Coinpaprika\Tests
 *
 * @author Krzysztof Przybyszewski <kprzybyszewski@greywizard.com>
 */
class ClientTest extends TestCase
{
    const TICKER_RESPONSE = [
        'id'        => 'my-coin',
        'name'      => 'MySuperCoin',
        'symbol'    => 'MSC',
        'rank'      => '1',
        'price_usd' => '6452.6115857',
        'price_btc' => '1',
        'volume_24h_usd' => '4173247439',
        'market_cap_usd' => '111498463272',
        'circulating_supply' => '100',
        'total_supply' => '55',
        'max_supply' => '20',
        'percent_change_1h' => '-2.4',
        'percent_change_24h' => '-0.01',
        'percent_change_7d' => '2.55',
        'last_updated' => '1537875087'
    ];

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
        $expectedResponse = static::TICKER_RESPONSE;

        $cacheDir = __DIR__.'/../../var/cache/';
        $client = new Client(
            $cacheDir,
            $this->getHttpClientMockWithResponse($expectedResponse)
        );

        $ticker = $client->getTickerByCoinId('my-coin');

        $this->assertInstanceOf(Ticker::class, $ticker);
        $this->assertTicker($ticker, $expectedResponse);

        $this->assertTrue(file_exists($cacheDir.'metadata/'));
    }

    public function testTickers(): void
    {
        $expectedResponse = [
            static::TICKER_RESPONSE,
            array_merge(static::TICKER_RESPONSE, ['id' => 'test', 'name' => 'test'])
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
            ['id' => 'coin1', 'name' => 'coin1', 'symbol' => 'C1'],
            ['id' => 'coin2', 'name' => 'coin2', 'symbol' => 'C2'],
            ['id' => 'coin3', 'name' => 'coin3', 'symbol' => 'C3']
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

        $client = new Client(null, $this->getHttpClientMockWithResponse($expectedResponse));
        $client->getTickerByCoinId('xxx');
    }

    /**
     * @expectedException \Coinpaprika\Exception\RateLimitExceededException
     */
    public function testRateLimitExceeded(): void
    {
        $client = new Client(null, $this->getHttpClientMockWithResponse([], 429));
        $client->getTickerByCoinId('btc-bitcoin');
    }

    private function assertCoin(Coin $coin, array $expectedResponse): void
    {
        $this->assertEquals($expectedResponse['id'], $coin->getId());
        $this->assertEquals($expectedResponse['name'], $coin->getName());
        $this->assertEquals($expectedResponse['symbol'], $coin->getSymbol());
    }

    private function assertTicker(Ticker $ticker, array $expectedResponse): void
    {
        $this->assertEquals($expectedResponse['id'], $ticker->getId());
        $this->assertEquals($expectedResponse['name'], $ticker->getName());
        $this->assertEquals($expectedResponse['symbol'], $ticker->getSymbol());
        $this->assertEquals($expectedResponse['rank'], $ticker->getRank());
        $this->assertEquals($expectedResponse['price_usd'], $ticker->getPriceUSD());
        $this->assertEquals($expectedResponse['price_btc'], $ticker->getPriceBTC());
        $this->assertEquals($expectedResponse['volume_24h_usd'], $ticker->getVolume24hUSD());
        $this->assertEquals($expectedResponse['market_cap_usd'], $ticker->getMarketCapUSD());
        $this->assertEquals($expectedResponse['circulating_supply'], $ticker->getCirculatingSupply());
        $this->assertEquals($expectedResponse['total_supply'], $ticker->getTotalSupply());
        $this->assertEquals($expectedResponse['max_supply'], $ticker->getMaxSupply());
        $this->assertEquals($expectedResponse['percent_change_1h'], $ticker->getPercentChange1h());
        $this->assertEquals($expectedResponse['percent_change_24h'], $ticker->getPercentChange24h());
        $this->assertEquals($expectedResponse['percent_change_7d'], $ticker->getPercentChange7d());
        $this->assertEquals($expectedResponse['last_updated'], $ticker->getLastUpdated());
    }

    private function getHttpClientMockWithResponse(array $responseArray, int $httpCode = 200): MockObject
    {
        $responseBody = json_encode($responseArray);
        $responseMock = $this->createMock(ResponseInterface::class);
        $httpClientMock = $this->createMock(\GuzzleHttp\Client::class);

        $responseMock
            ->method('getBody')
            ->willReturn($responseBody)
        ;

        $responseMock
            ->method('getStatusCode')
            ->willReturn($httpCode)
        ;

        $httpClientMock
            ->method('request')
            ->withAnyParameters()
            ->willReturn($responseMock)
        ;

        return $httpClientMock;
    }
}
