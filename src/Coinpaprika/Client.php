<?php

namespace Coinpaprika;

use Coinpaprika\Exception\RateLimitExceededException;
use Coinpaprika\Exception\ResponseErrorException;
use Coinpaprika\Model\Coin;
use Coinpaprika\Model\GlobalStats;
use Coinpaprika\Model\Ticker;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\Serializer;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Client
 *
 * @package Coinpaprika
 *
 * @author Krzysztof Przybyszewski <kprzybyszewski@greywizard.com>
 */
class Client
{
    const BASE_URL = 'https://api.coinpaprika.com/%ver%/';

    /**
     * @var string
     */
    private $apiVersion;

    /**
     * @var \GuzzleHttp\Client
     */
    private $httpClient;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * Client constructor.
     *
     * @param string|null        $cacheDir
     * @param \GuzzleHttp\Client $httpClient
     */
    public function __construct(
        string $cacheDir = null,
        \GuzzleHttp\Client $httpClient = null
    ) {
        $serializerBuilder = SerializerBuilder::create()
            ->addMetadataDir(__DIR__.'/Resource/config/serializer');

        if ($cacheDir !== null) {
            $serializerBuilder->setCacheDir($cacheDir);
        }

        $this->apiVersion = 'v1';
        $this->serializer = $serializerBuilder->build();

        if ($httpClient === null) {
            $httpClient = new \GuzzleHttp\Client();
        }

        $this->httpClient = $httpClient;
    }

    /**
     * Get global stats
     *
     * @throws GuzzleException
     * @throws ResponseErrorException
     * @throws RateLimitExceededException
     *
     * @return GlobalStats
     */
    public function getGlobalStats(): GlobalStats
    {
        $response = $this->sendRequest('GET', $this->getEndpointUrl('global'));

        return $this->deserializeResponse($response, GlobalStats::class);
    }

    /**
     * Get tickers
     *
     * @throws GuzzleException
     * @throws ResponseErrorException
     * @throws RateLimitExceededException
     *
     * @return array|Ticker[]
     */
    public function getTickers(): array
    {
        $response = $this->sendRequest('GET', $this->getEndpointUrl('ticker'));

        return $this->deserializeResponse($response, sprintf('array<%s>', Ticker::class));
    }

    /**
     * Get coin`s ticker data
     *
     * @param string $id
     *
     * @throws GuzzleException
     * @throws ResponseErrorException
     * @throws RateLimitExceededException
     *
     * @return Ticker
     */
    public function getTickerByCoinId(string $id): Ticker
    {
        $response = $this->sendRequest(
            'GET',
            $this->getEndpointUrl(sprintf('ticker/%s', $id))
        );

        return $this->deserializeResponse($response, Ticker::class);
    }

    /**
     * Get coins list
     *
     * @throws GuzzleException
     * @throws ResponseErrorException
     * @throws RateLimitExceededException
     *
     * @return array|Coin[]
     */
    public function getCoins(): array
    {
        $response = $this->sendRequest('GET', $this->getEndpointUrl('coins'));

        return $this->deserializeResponse($response, sprintf('array<%s>', Coin::class));

    }

    /**
     * Get the endpoint URL.
     *
     * @param string $endpoint
     *
     * @return string
     */
    protected function getEndpointUrl(string $endpoint): string
    {
        return str_replace('%ver%', $this->getApiVersion(), static::BASE_URL).$endpoint;
    }

    /**
     * Get the API version
     *
     * @return string
     */
    public function getApiVersion(): string
    {
        return $this->apiVersion;
    }

    /**
     * Get the latest tag from git repository.
     *
     * @return string
     */
    public function getClientVersion(): string
    {
        $version = trim(`git describe --tags --abbrev=0`);

        return $version;
    }

    /**
     * @param string $method
     * @param string $url
     * @param array  $headers
     *
     * @return ResponseInterface
     * @throws GuzzleException
     */
    protected function sendRequest(string $method, string $url, array $headers = []): ResponseInterface
    {
        $defaultHeaders = [
            'User-Agent' => sprintf('Coinpaprika API Client - PHP (%s)', $this->getClientVersion())
        ];

        return $this->httpClient->request($method, $url, [
            'headers' => array_merge($defaultHeaders, $headers)
        ]);
    }

    /**
     * Unmarshal JSON
     *
     * @param ResponseInterface $response
     * @param string            $type
     *
     * @throws ResponseErrorException
     * @throws RateLimitExceededException
     *
     * @return mixed
     */
    protected function deserializeResponse(ResponseInterface $response, string $type)
    {
        $body = $response->getBody();

        // rate limit exceeded
        if ($response->getStatusCode() === 429) {
            throw new RateLimitExceededException('Response code from API 429. Rate limit exceeded.');
        }

        if (array_key_exists('error', $e = json_decode($body, true))) {
            throw new ResponseErrorException($e['error']);
        }

        return $this->serializer->deserialize($body, $type, 'json');
    }
}
