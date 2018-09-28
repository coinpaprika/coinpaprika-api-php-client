<?php

namespace Coinpaprika;

use Coinpaprika\Exception\InvalidResponseException;
use Coinpaprika\Exception\RateLimitExceededException;
use Coinpaprika\Exception\ResponseErrorException;
use Coinpaprika\Http\Request;
use Coinpaprika\Model\Coin;
use Coinpaprika\Model\GlobalStats;
use Coinpaprika\Model\Search;
use Coinpaprika\Model\Ticker;
use GuzzleHttp\Exception\ClientException;
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
     * @param   string|null         $cacheDir
     * @param   \GuzzleHttp\Client  $httpClient
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
     * @throws  GuzzleException
     * @throws  ResponseErrorException
     * @throws  RateLimitExceededException
     * @throws  InvalidResponseException
     *
     * @return  GlobalStats
     */
    public function getGlobalStats(): GlobalStats
    {
        $response = $this->sendRequest(
            Request::METHOD_GET,
            $this->getEndpointUrl('global')
        );

        return $this->response($response, GlobalStats::class);
    }

    /**
     * Get tickers
     *
     * @throws  GuzzleException
     * @throws  ResponseErrorException
     * @throws  RateLimitExceededException
     * @throws  InvalidResponseException
     *
     * @return  array|Ticker[]
     */
    public function getTickers(): array
    {
        $response = $this->sendRequest(
            Request::METHOD_GET,
            $this->getEndpointUrl('ticker')
        );

        return $this->response($response, sprintf('array<%s>', Ticker::class));
    }

    /**
     * Get coin`s ticker data
     *
     * @param   string $id
     *
     * @throws  GuzzleException
     * @throws  ResponseErrorException
     * @throws  RateLimitExceededException
     * @throws  InvalidResponseException
     *
     * @return  Ticker
     */
    public function getTickerByCoinId(string $id): Ticker
    {
        $response = $this->sendRequest(
            Request::METHOD_GET,
            $this->getEndpointUrl(sprintf('ticker/%s', $id))
        );

        return $this->response($response, Ticker::class);
    }

    /**
     * Get coins list
     *
     * @throws  GuzzleException
     * @throws  ResponseErrorException
     * @throws  RateLimitExceededException
     * @throws  InvalidResponseException
     *
     * @return  array|Coin[]
     */
    public function getCoins(): array
    {
        $response = $this->sendRequest(
            Request::METHOD_GET,
            $this->getEndpointUrl('coins')
        );

        return $this->response($response, sprintf('array<%s>', Coin::class));

    }

    /**
     * @param   string     $query       Search query string
     * @param   array|null $categories  When null it defaults to all possible categories
     * @param   int        $limit       Per category limit
     *
     * @throws  InvalidResponseException
     * @throws  RateLimitExceededException
     * @throws  ResponseErrorException
     * @throws  GuzzleException
     *
     * @return Search
     */
    public function search(string $query, array $categories = null, int $limit = null): Search
    {
        $params = array_filter([
            'q' => $query,
            'c' => $categories ? implode(',', $categories) : null,
            'limit' => $limit
        ]);

        $response = $this->sendRequest(
            Request::METHOD_GET,
            $this->getEndpointUrl('search'),
            $params
        );

        return $this->response($response, Search::class);
    }

    /**
     * Get the API version
     *
     * @return  string
     */
    public function getApiVersion(): string
    {
        return $this->apiVersion;
    }

    /**
     * Get the endpoint URL.
     *
     * @param   string  $endpoint
     *
     * @return  string
     */
    protected function getEndpointUrl(string $endpoint): string
    {
        return str_replace('%ver%', $this->getApiVersion(), static::BASE_URL).$endpoint;
    }

    /**
     * @param   string $method
     * @param   string $url
     * @param   array  $params
     * @param   array  $headers
     *
     * @return  ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function sendRequest(
        string $method,
        string $url,
        array $params = [],
        array $headers = []
    ): ResponseInterface {

        $defaultHeaders = [
            'User-Agent' => 'Coinpaprika API Client - PHP'
        ];

        if (Request::METHOD_GET === $method) {
            $params = http_build_query($params);

            $url = sprintf('%s?%s', $url, $params);
        }

        try {

            return $this->httpClient->request($method, $url, [
                'headers' => array_merge($defaultHeaders, $headers)
            ]);

        } catch (ClientException $e) {
            return $e->getResponse();
        }
    }

    /**
     * @param   ResponseInterface   $response
     *
     * @throws  InvalidResponseException
     * @throws  RateLimitExceededException
     * @throws  ResponseErrorException
     */
    protected function validateResponse(ResponseInterface $response): void
    {
        $statusCode = $response->getStatusCode();

        // rate limit exceeded
        if ($statusCode === 429) {
            throw new RateLimitExceededException('Response code from API 429. Rate limit exceeded.');
        }

        // check for errors
        if ($statusCode >= 400 && $statusCode <= 500) {

            if (array_key_exists('error', $e = json_decode($response->getBody(), true))) {

                throw new ResponseErrorException(sprintf(
                    'Response code: %s, error: %s',
                    $statusCode,
                    $e['error']
                ));
            }

            throw new InvalidResponseException(sprintf(
                'Bad response from a server - status code: %s, but error field does not exists.',
                $statusCode
            ));
        }
    }

    /**
     * Unmarshal JSON
     *
     * @param   ResponseInterface $response
     * @param   string            $type
     *
     * @throws  ResponseErrorException
     * @throws  RateLimitExceededException
     * @throws  InvalidResponseException
     *
     * @see     https://api.coinpaprika.com/#section/Errors
     *
     * @return  mixed
     */
    protected function response(ResponseInterface $response, string $type)
    {
        $this->validateResponse($response);

        return $this->serializer->deserialize($response->getBody(), $type, 'json');
    }
}
