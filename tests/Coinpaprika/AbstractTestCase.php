<?php

namespace Coinpaprika\Tests;

use Coinpaprika\Model\Coin;
use Coinpaprika\Model\Exchange;
use Coinpaprika\Model\Ico;
use Coinpaprika\Model\Person;
use Coinpaprika\Model\Tag;
use Coinpaprika\Model\Ticker;
use GuzzleHttp\Exception\ClientException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class ClientTest
 *
 * @package \Coinpaprika\Tests
 *
 * @author Krzysztof Przybyszewski <kprzybyszewski@greywizard.com>
 */
abstract class AbstractTestCase extends TestCase
{
    /**
     * @return array
     */
    protected function getCoinStructure(): array
    {
        return [
            'id' => 'coin3',
            'name' => 'coin3',
            'symbol' => 'C3',
            'rank' => 3,
            'is_new' => false,
            'is_active' => true
        ];
    }

    /**
     * @param   Coin $coin
     * @param   array $expectedResponse
     */
    protected function assertCoin(Coin $coin, array $expectedResponse): void
    {
        $this->assertEquals($expectedResponse['id'], $coin->getId());
        $this->assertEquals($expectedResponse['name'], $coin->getName());
        $this->assertEquals($expectedResponse['symbol'], $coin->getSymbol());
        $this->assertEquals($expectedResponse['rank'], $coin->getRank());
        $this->assertEquals($expectedResponse['is_new'], $coin->isNew());
        $this->assertEquals($expectedResponse['is_active'], $coin->isActive());
    }

    /**
     * @return array
     */
    protected function getTickerStructure(): array
    {
        return [
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
    }

    /**
     * @param   Ticker $ticker
     * @param   array $expectedResponse
     */
    protected function assertTicker(Ticker $ticker, array $expectedResponse): void
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

    /**
     * @return array
     */
    protected function getExchangeStructure(): array
    {
        return [
            'id' => 'allcoin',
            'name' => 'Allcoin',
            'rank' => 30
        ];
    }

    /**
     * @param   Exchange $exchange
     * @param   array $expectedResponse
     */
    protected function assertExchange(Exchange $exchange, array $expectedResponse): void
    {
        $this->assertEquals($expectedResponse['id'], $exchange->getId());
        $this->assertEquals($expectedResponse['name'], $exchange->getName());
        $this->assertEquals($expectedResponse['rank'], $exchange->getRank());
    }

    /**
     * @return array
     */
    protected function getIcoStructure(): array
    {
        return [
            'id' => 'acad-academy',
            'name' => 'Academy',
            'symbol' => 'ACAD',
            'is_new' => false
        ];
    }

    /**
     * @param   Ico $ico
     * @param   array $expectedResponse
     */
    protected function assertIco(Ico $ico, array $expectedResponse): void
    {
        $this->assertEquals($expectedResponse['id'], $ico->getId());
        $this->assertEquals($expectedResponse['name'], $ico->getName());
        $this->assertEquals($expectedResponse['symbol'], $ico->getSymbol());
        $this->assertEquals($expectedResponse['is_new'], $ico->isNew());
    }

    /**
     * @return array
     */
    protected function getPersonStructure(): array
    {
        return [
            'id' => 'anthony-di-iorio',
            'name' => 'Anthony Di Iorio',
            'teams_count' => 5
        ];
    }

    /**
     * @param   Person $person
     * @param   array $expectedResponse
     */
    protected function assertPerson(Person $person, array $expectedResponse): void
    {
        $this->assertEquals($expectedResponse['id'], $person->getId());
        $this->assertEquals($expectedResponse['name'], $person->getName());
        $this->assertEquals($expectedResponse['teams_count'], $person->getTeamsCount());
    }

    /**
     * @return array
     */
    protected function getTagStructure(): array
    {
        return [
            'id' => 'commerce-advertising',
            'name' => 'Commerce & Advertising',
            'coin_counter' => 50,
            'ico_counter' => 28
        ];
    }

    /**
     * @param   Tag $tag
     * @param   array $expectedResponse
     */
    protected function assertTag(Tag $tag, array $expectedResponse): void
    {
        $this->assertEquals($expectedResponse['id'], $tag->getId());
        $this->assertEquals($expectedResponse['name'], $tag->getName());
        $this->assertEquals($expectedResponse['coin_counter'], $tag->getCoinCounter());
        $this->assertEquals($expectedResponse['ico_counter'], $tag->getIcoCounter());
    }

    /**
     * @return array
     */
    protected function getSearchExpectedResponse(): array
    {
        return [
            'currencies' => [
                $this->getCoinStructure(),
                array_merge($this->getCoinStructure(), ['name' => 'bit_my_coin']),
                array_merge($this->getCoinStructure(), ['name' => 'my_bit_coin'])
            ],
            'exchanges' => [
                $this->getExchangeStructure(),
                array_merge($this->getExchangeStructure(), ['rank' => 10, 'name' => 'Bitmex'])
            ],
            'icos' => [
                $this->getIcoStructure(),
                array_merge($this->getIcoStructure(), ['name' => 'bitico'])
            ],
            'people' => [
                $this->getPersonStructure(),
                array_merge($this->getIcoStructure(), ['name' => 'Bit Surname'])
            ],
            'tags' => [
                $this->getTagStructure()
            ]
        ];
    }

    protected function getHttpClientMockWithResponse(array $responseArray, int $httpCode = 200): MockObject
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

        if ($httpCode >= 400) {

            $exception = new ClientException(
                sprintf('HTTP code: %s', $httpCode),
                $this->createMock(RequestInterface::class),
                $responseMock
            );

            $httpClientMock
                ->method('request')
                ->willThrowException($exception);
        }

        return $httpClientMock;
    }
}
