# Changelog

All notable changes to this project are documented here. Format follows
[Keep a Changelog](https://keepachangelog.com/en/1.1.0/) and the project uses
[Semantic Versioning](https://semver.org/).

## [1.5.0] - 2026-04-13

### ⚠ Breaking changes

- **Constructor signature reordered** to `__construct(?string $apiKey, ?\GuzzleHttp\Client $httpClient, ?string $cacheDir)`. Previously `$cacheDir` was first. Pro API setup is now a one-liner: `new \Coinpaprika\Client('YOUR_KEY')`. Callers that passed positional `$cacheDir` must use the named-argument form: `new \Coinpaprika\Client(cacheDir: 'my/cache/dir')`.
- **`getCoinMarkets()` return type** widened from `array` to `Coinpaprika\Model\Market[]`. Callers still treating the value as a plain array continue to work (`Market[]` is an `array`), but per-item access is now via typed getters (`$market->getPair()`) rather than raw keys (`$row['pair']`).
- **`getExchanges()` / `getExchangeById()` return types** widened from `array` to `Coinpaprika\Model\ExchangeDetail[]` / `ExchangeDetail`. Same migration note as above for per-item access. Note: the existing `Coinpaprika\Model\Exchange` model used inside search results is unchanged.
- **`getKeyInfo()` return type** widened from `array` to `Coinpaprika\Model\KeyInfo`.
- **`getCoinsMappings()` return type** widened from `array` (of associative arrays) to `Coinpaprika\Model\CoinMapping[]`.
- **`getChangelogIds()` signature & return type**: now accepts an optional `$params` array (for the `page` query param) and returns `Coinpaprika\Model\ChangelogEntry[]` instead of `array`.

### Fixed

- `getTickers()` and `getTickerByCoinId()` now hit `/v1/tickers` and `/v1/tickers/{id}` respectively. The old `/v1/ticker` paths have been deprecated server-side and were returning 404 — both methods were broken against the live API.
- `getTokenMeta()` no longer throws `JMS\Serializer\Exception\RuntimeException` against real responses. Root cause: the `CoinMeta` model had no serializer metadata file; added `Coinpaprika.Model.CoinMeta.yml` (and a companion `Coinpaprika.Model.TeamMember.yml` for the nested `team` field).
- **Trailing `?` on param-less GET requests.** `sendRequest` used `http_build_query()` unconditionally; for an empty params array that produced an empty string, leaving a bare `?` suffix on every URL. Now the `query` option is only added when params are non-empty.
- **Silent loss of 5xx errors.** The previous implementation had `try/catch (ClientException)` which only swallowed 4xx — a 500 crashed the application. Replaced with Guzzle's `http_errors => false` so every non-2xx response goes through `validateResponse`, which translates status codes into the appropriate `ResponseErrorException` / `InvalidResponseException` / `RateLimitExceededException`.
- **Potential fatal in `validateResponse`** when an error body wasn't JSON (e.g. HTML 500 page). `array_key_exists('error', json_decode($html, true))` would call `array_key_exists` on `null` and fatal under PHP 8. Added `is_array($decoded) &&` guard.
- **Response body read twice.** `response()` / `responseRaw()` called `$response->getBody()` before and again after `validateResponse`. With non-seekable streams the second read returns `""`, silently producing empty-object deserialization. Body is now read once and passed through.
- **`setApiKey()`** now has a `: void` return type for consistency.
- **`getTokenMeta()` method-header docblock** corrected (was copy-pasted from `OHLCV`).

### Added

- **`Coinpaprika\Model\Market`** — typed model for `/v1/coins/{id}/markets` entries. Flat common fields typed; nested per-currency `quotes` map exposed as `array` (shape depends on requested quote currencies).
- **`Coinpaprika\Model\ExchangeDetail`** — typed model for `/v1/exchanges` and `/v1/exchanges/{id}`. Separate from the existing `Exchange` model (which Search uses) so Search deserialization is unaffected.
- **`Coinpaprika\Model\KeyInfo`** (with nested `KeyInfo\Usage` + `Usage\CurrentMonth`) — typed model for `/v1/key/info`. Shape verified against Coinpaprika docs. `getRequestsMade()` / `getRequestsLeft()` return `-1` on unlimited plans per the server's sentinel convention.
- **`Coinpaprika\Model\CoinMapping`** — typed model for `/v1/coins/mappings` entries (Business/Enterprise plan).
- **`Coinpaprika\Model\ChangelogEntry`** — typed model for `/v1/changelog/ids` entries. `getChangelogIds()` now accepts a `$params` array with optional `page` (100 entries per page).
- **`examples/stressTest.php`** — load tool that cycles through 10 representative endpoints, reports per-endpoint mean latency + global min/p50/p95/p99/max, tracks memory delta, and aborts on consecutive 429 responses. Configurable via `STRESS_ITERATIONS`, `STRESS_DELAY_MS`, `STRESS_MAX_429` env vars.
- **`Coinpaprika\Client::VERSION`** constant (`'1.5.0'`). Used in the `User-Agent` header, which is now `Coinpaprika API Client - PHP/{VERSION}` for server-side analytics.
- **Deserialization tests** for `CoinMeta`, `Market`, `ExchangeDetail` using realistic fixtures.
- **Fixture-based decoding tests** for Pro-only endpoints (`getKeyInfo`, `getCoinsMappings`, `getChangelogIds`) — these methods still return `array` because their response shapes aren't publicly documented enough to commit to a typed model, but the tests pin end-to-end decoding and URL/header construction.
- **`examples/smokeTest.php`** — standalone script that exercises the client against the live API and verifies happy paths, query-param round-tripping, and error-wrapping behavior. Run with `php examples/smokeTest.php` (free API) or `API_KEY=xxx php examples/smokeTest.php` (Pro).
- **Guzzle `MockHandler`-based test helper (`makeGuzzleWithMock`)** in `AbstractTestCase`. New URL/query/header tests use this instead of the older `createMock(GuzzleClient)` path because it captures the actual outbound PSR-7 request.

### Changed

- Migrated error-path tests from deprecated `@expectedException` PHPDoc annotations to `$this->expectException()` (required by PHPUnit 10 which `composer.json` allows).
- The old test mock helper (`getHttpClientMockWithResponse`) no longer simulates `ClientException` throwing for 4xx responses — it now mirrors the real `http_errors => false` behavior (returns the response for every status). `getBody()` returns a real `StreamInterface` via `Utils::streamFor()` to satisfy PHPUnit 10's strict return-type checks.

### Endpoints vs docs cross-check

Compared each client endpoint against [docs.coinpaprika.com/llms.txt](https://docs.coinpaprika.com/llms.txt) and spot-checked discrepancies against the live API. Current findings (for the record — no action taken):

- `priceConverter()` hits `/v1/price-converter`. Docs index recommends `/v1/tools/price-converter`, but that path returns 404 live while the current path returns 200. **Client is correct; docs are stale.**
- `getOHLCV()` hits `/v1/coins/{id}/ohlcv/latest`. Not in docs index but the path works. **Client is correct.**
- `getCoinTwitter()` hits `/v1/coins/{id}/twitter`. Docs mark this as deprecated but it still returns data. Kept for backwards compatibility; expect removal in a future tag.
- `getIcos()` hits `/v1/icos`. Not in docs index but returns 200 live. **Client is correct.**
- Docs list `/v1/coins/{id}/ohlcv/last_full_day` which the client does not expose — live API returns 404 for that path, so not implementing.

### Known non-goals

- None.

## [1.4.0] - 2026-04-12

- Added 19 endpoints covering Coinpaprika's extended surface area: Twitter/events/exchanges/markets for coins, historical/today OHLCV, coin mappings, people, tags, exchanges, price converter, contract platforms, key info, changelog IDs.
- Bumped minimum PHP to 8.0; updated Guzzle / JMS Serializer / Symfony YAML constraints.
