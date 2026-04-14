<?php

namespace Coinpaprika;

use Coinpaprika\Exception\InvalidResponseException;
use Coinpaprika\Exception\PaymentRequiredException;
use Coinpaprika\Exception\RateLimitExceededException;
use Coinpaprika\Exception\ResponseErrorException;
use Coinpaprika\Http\Request;
use Coinpaprika\Model\Coin;
use Coinpaprika\Model\ChangelogEntry;
use Coinpaprika\Model\CoinMapping;
use Coinpaprika\Model\CoinMeta;
use Coinpaprika\Model\ExchangeDetail;
use Coinpaprika\Model\GlobalStats;
use Coinpaprika\Model\HistoricalTicker;
use Coinpaprika\Model\Ico;
use Coinpaprika\Model\KeyInfo;
use Coinpaprika\Model\Market;
use Coinpaprika\Model\OHLCV;
use Coinpaprika\Model\Search;
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
    const VERSION = '1.5.0';

    const BASE_URL = 'https://api.coinpaprika.com/%ver%/';

    const PRO_BASE_URL = 'https://api-pro.coinpaprika.com/%ver%/';

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
     * @var string|null
     */
    private $apiKey;


    /**
     * Client constructor.
     *
     * @param  string|null              $apiKey     Pro API key. When set, requests go to api-pro.coinpaprika.com.
     * @param  \GuzzleHttp\Client|null  $httpClient Override HTTP client (useful for tests or custom middleware).
     * @param  string|null              $cacheDir   Optional cache dir for JMS Serializer metadata.
     */
    public function __construct(
        ?string $apiKey = null,
        ?\GuzzleHttp\Client $httpClient = null,
        ?string $cacheDir = null
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

        $this->apiKey = $apiKey;
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
            $this->getEndpointUrl('tickers')
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
            $this->getEndpointUrl(sprintf('tickers/%s', $id))
        );

        return $this->response($response, Ticker::class);
    }

    /**
     * Get coin`s historical ticker data
     *
     * @param   string $id
     * @param   string $start
     * @param   string $interval
     *
     * @throws  GuzzleException
     * @throws  ResponseErrorException
     * @throws  RateLimitExceededException
     * @throws  InvalidResponseException
     *
     * @return  HistoricalTicker[]
     */
    public function getHistoricalTickerByCoinId(string $id, string $start, string $interval): array
    {
        $response = $this->sendRequest(
            Request::METHOD_GET,
            $this->getEndpointUrl(sprintf('tickers/%s/historical', $id)),
            ['start' => $start, 'interval' => $interval]
        );

        return $this->response($response, sprintf('array<%s>', HistoricalTicker::class));
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
     *
     * @param array $parameters API url query params array
     *
     * @throws \Coinpaprika\Exception\InvalidResponseException
     * @throws \Coinpaprika\Exception\RateLimitExceededException
     * @throws \Coinpaprika\Exception\ResponseErrorException
     * @throws \GuzzleHttp\Exception\GuzzleException
     *
     * @return array|Ico[]
     */
    public function getIcos(array $parameters = []): array
    {
        $response = $this->sendRequest(
            Request::METHOD_GET,
            $this->getEndpointUrl('icos'),
            $parameters
        );

        return $this->response($response, sprintf('array<%s>', Ico::class));
    }

    /**
     * Get latest OHLCV data for a coin.
     *
     * @param   string $id
     * @param   string $interval
     *
     * @throws  GuzzleException
     * @throws  ResponseErrorException
     * @throws  RateLimitExceededException
     * @throws  InvalidResponseException
     *
     * @return  OHLCV
     */
    public function getOHLCV(string $id, string $interval): OHLCV
    {
        $response = $this->sendRequest(
            Request::METHOD_GET,
            $this->getEndpointUrl(sprintf('coins/%s/ohlcv/latest', $id)),
            ['interval' => $interval]
        );

        return $this->response($response, OHLCV::class);
    }

    /**
     * Get coin metadata.
     *
     * @param   string $id
     *
     * @throws  GuzzleException
     * @throws  ResponseErrorException
     * @throws  RateLimitExceededException
     * @throws  InvalidResponseException
     *
     * @return  CoinMeta
     */
    public function getTokenMeta(string $id): CoinMeta
    {
        $response = $this->sendRequest(
            Request::METHOD_GET,
            $this->getEndpointUrl(sprintf('coins/%s', $id))
        );

        return $this->response($response, CoinMeta::class);
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
    public function search(string $query, ?array $categories = null, ?int $limit = null): Search
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
     * Get coin's Twitter timeline
     *
     * @param string $coinId
     * @return array
     */
    public function getCoinTwitter(string $coinId): array
    {
        return $this->responseRaw(
            $this->sendRequest(Request::METHOD_GET, $this->getEndpointUrl(sprintf('coins/%s/twitter', $coinId)))
        );
    }

    /**
     * Get coin's events
     *
     * @param string $coinId
     * @return array
     */
    public function getCoinEvents(string $coinId): array
    {
        return $this->responseRaw(
            $this->sendRequest(Request::METHOD_GET, $this->getEndpointUrl(sprintf('coins/%s/events', $coinId)))
        );
    }

    /**
     * Get exchanges where a coin is listed
     *
     * @param string $coinId
     * @return array
     */
    public function getCoinExchanges(string $coinId): array
    {
        return $this->responseRaw(
            $this->sendRequest(Request::METHOD_GET, $this->getEndpointUrl(sprintf('coins/%s/exchanges', $coinId)))
        );
    }

    /**
     * Get markets for a coin
     *
     * @param string $coinId
     * @param array  $params  Optional: quotes
     * @return Market[]
     */
    public function getCoinMarkets(string $coinId, array $params = []): array
    {
        $response = $this->sendRequest(
            Request::METHOD_GET,
            $this->getEndpointUrl(sprintf('coins/%s/markets', $coinId)),
            $params
        );

        return $this->response($response, sprintf('array<%s>', Market::class));
    }

    /**
     * Get historical OHLCV data for a coin
     *
     * @param string $coinId
     * @param string $start Start date (ISO-8601 or yyyy-mm-dd)
     * @param array $params Optional: end, limit, quote
     * @return array
     */
    public function getHistoricalOHLCV(string $coinId, string $start, array $params = []): array
    {
        $params['start'] = $start;
        return $this->responseRaw(
            $this->sendRequest(Request::METHOD_GET, $this->getEndpointUrl(sprintf('coins/%s/ohlcv/historical', $coinId)), $params)
        );
    }

    /**
     * Get today's OHLCV data for a coin (updates until day close)
     *
     * @param string $coinId
     * @param array $params Optional: quote
     * @return array
     */
    public function getTodayOHLCV(string $coinId, array $params = []): array
    {
        return $this->responseRaw(
            $this->sendRequest(Request::METHOD_GET, $this->getEndpointUrl(sprintf('coins/%s/ohlcv/today', $coinId)), $params)
        );
    }

    /**
     * Get coin ID mappings to other data providers (Business/Enterprise plan).
     *
     * @param  array $params Optional: limit
     * @return CoinMapping[]
     */
    public function getCoinsMappings(array $params = []): array
    {
        $response = $this->sendRequest(
            Request::METHOD_GET,
            $this->getEndpointUrl('coins/mappings'),
            $params
        );

        return $this->response($response, sprintf('array<%s>', CoinMapping::class));
    }

    /**
     * Get person details by ID
     *
     * @param string $personId
     * @return array
     */
    public function getPersonById(string $personId): array
    {
        return $this->responseRaw(
            $this->sendRequest(Request::METHOD_GET, $this->getEndpointUrl(sprintf('people/%s', $personId)))
        );
    }

    /**
     * Get all tags
     *
     * @param array $params Optional: additional_fields
     * @return array
     */
    public function getTags(array $params = []): array
    {
        return $this->responseRaw(
            $this->sendRequest(Request::METHOD_GET, $this->getEndpointUrl('tags'), $params)
        );
    }

    /**
     * Get tag by ID
     *
     * @param string $tagId
     * @param array $params Optional: additional_fields
     * @return array
     */
    public function getTagById(string $tagId, array $params = []): array
    {
        return $this->responseRaw(
            $this->sendRequest(Request::METHOD_GET, $this->getEndpointUrl(sprintf('tags/%s', $tagId)), $params)
        );
    }

    /**
     * Get list of exchanges
     *
     * @param array $params Optional: quotes
     * @return ExchangeDetail[]
     */
    public function getExchanges(array $params = []): array
    {
        $response = $this->sendRequest(
            Request::METHOD_GET,
            $this->getEndpointUrl('exchanges'),
            $params
        );

        return $this->response($response, sprintf('array<%s>', ExchangeDetail::class));
    }

    /**
     * Get exchange by ID
     *
     * @param string $exchangeId
     * @param array  $params      Optional: quotes
     * @return ExchangeDetail
     */
    public function getExchangeById(string $exchangeId, array $params = []): ExchangeDetail
    {
        $response = $this->sendRequest(
            Request::METHOD_GET,
            $this->getEndpointUrl(sprintf('exchanges/%s', $exchangeId)),
            $params
        );

        return $this->response($response, ExchangeDetail::class);
    }

    /**
     * Get markets on an exchange
     *
     * @param string $exchangeId
     * @param array $params Optional: quotes
     * @return array
     */
    public function getExchangeMarkets(string $exchangeId, array $params = []): array
    {
        return $this->responseRaw(
            $this->sendRequest(Request::METHOD_GET, $this->getEndpointUrl(sprintf('exchanges/%s/markets', $exchangeId)), $params)
        );
    }

    /**
     * Convert between currencies
     *
     * @param array $params Required: base_currency_id, quote_currency_id, amount
     * @return array
     */
    public function priceConverter(array $params): array
    {
        return $this->responseRaw(
            $this->sendRequest(Request::METHOD_GET, $this->getEndpointUrl('price-converter'), $params)
        );
    }

    /**
     * Get list of contract platforms
     *
     * @return array
     */
    public function getPlatforms(): array
    {
        return $this->responseRaw(
            $this->sendRequest(Request::METHOD_GET, $this->getEndpointUrl('contracts'))
        );
    }

    /**
     * Get all contracts on a platform
     *
     * @param string $platformId e.g., 'eth-ethereum'
     * @return array
     */
    public function getContracts(string $platformId): array
    {
        return $this->responseRaw(
            $this->sendRequest(Request::METHOD_GET, $this->getEndpointUrl(sprintf('contracts/%s', $platformId)))
        );
    }

    /**
     * Get ticker data by contract address
     *
     * @param string $platformId
     * @param string $contractAddress
     * @param array $params Optional: quotes
     * @return array
     */
    public function getTickerByContract(string $platformId, string $contractAddress, array $params = []): array
    {
        return $this->responseRaw(
            $this->sendRequest(Request::METHOD_GET, $this->getEndpointUrl(sprintf('contracts/%s/%s', $platformId, $contractAddress)), $params)
        );
    }

    /**
     * Get historical ticker data by contract address
     *
     * @param string $platformId
     * @param string $contractAddress
     * @param array $params Required: start. Optional: end, limit, quote, interval
     * @return array
     */
    public function getHistoricalByContract(string $platformId, string $contractAddress, array $params = []): array
    {
        return $this->responseRaw(
            $this->sendRequest(Request::METHOD_GET, $this->getEndpointUrl(sprintf('contracts/%s/%s/historical', $platformId, $contractAddress)), $params)
        );
    }

    /**
     * Get API key plan & usage information (requires API key).
     *
     * @return KeyInfo
     */
    public function getKeyInfo(): KeyInfo
    {
        $response = $this->sendRequest(
            Request::METHOD_GET,
            $this->getEndpointUrl('key/info')
        );

        return $this->response($response, KeyInfo::class);
    }

    /**
     * Get coin ID rename history (paid plans only).
     *
     * @param  array $params Optional: page (default 1, 100 per page)
     * @return ChangelogEntry[]
     */
    public function getChangelogIds(array $params = []): array
    {
        $response = $this->sendRequest(
            Request::METHOD_GET,
            $this->getEndpointUrl('changelog/ids'),
            $params
        );

        return $this->response($response, sprintf('array<%s>', ChangelogEntry::class));
    }

    public function setApiKey(string $apiKey): void
    {
        $this->apiKey = $apiKey;
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
        $baseUrl = $this->apiKey ? static::PRO_BASE_URL : static::BASE_URL;

        return str_replace('%ver%', $this->getApiVersion(), $baseUrl).$endpoint;
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
            'User-Agent' => sprintf('Coinpaprika API Client - PHP/%s', self::VERSION)
        ];

        if ($this->apiKey) {
            $defaultHeaders['Authorization'] = $this->apiKey;
        }

        $options = [
            'headers' => array_merge($defaultHeaders, $headers),
            'http_errors' => false,
        ];

        if (!empty($params)) {
            $options['query'] = $params;
        }

        return $this->httpClient->request($method, $url, $options);
    }

    /**
     * @param   ResponseInterface   $response
     * @param   string              $body      Response body already read as a string.
     *
     * @throws  InvalidResponseException
     * @throws  RateLimitExceededException
     * @throws  ResponseErrorException
     */
    protected function validateResponse(ResponseInterface $response, string $body): void
    {
        $statusCode = $response->getStatusCode();

        // rate limit exceeded
        if ($statusCode === 429) {
            throw new RateLimitExceededException('Response code from API 429. Rate limit exceeded.');
        }

        if ($statusCode === 403) {
            throw new PaymentRequiredException('Payment required for this api key.');
        }

        // check for errors
        if ($statusCode >= 400 && $statusCode <= 500) {
            $decoded = json_decode($body, true);

            if (is_array($decoded) && array_key_exists('error', $decoded)) {
                throw new ResponseErrorException(sprintf(
                    'Response code: %s, error: %s',
                    $statusCode,
                    $decoded['error']
                ));
            }

            throw new InvalidResponseException(sprintf(
                'Bad response from a server - status code: %s, but error field does not exists.',
                $statusCode
            ));
        }
    }

    /**
     * Unmarshal JSON via JMS Serializer (typed models)
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
        $body = (string) $response->getBody();
        $this->validateResponse($response, $body);

        return $this->serializer->deserialize($body, $type, 'json');
    }

    /**
     * Return raw decoded JSON array
     *
     * @param   ResponseInterface $response
     *
     * @throws  ResponseErrorException
     * @throws  RateLimitExceededException
     * @throws  InvalidResponseException
     *
     * @return  array
     */
    protected function responseRaw(ResponseInterface $response): array
    {
        $body = (string) $response->getBody();
        $this->validateResponse($response, $body);

        $decoded = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidResponseException(
                sprintf('Invalid JSON response: %s', json_last_error_msg())
            );
        }

        return $decoded;
    }
}
