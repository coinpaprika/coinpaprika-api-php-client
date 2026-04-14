<?php

namespace Coinpaprika\Model;

/**
 * Market entry returned by /v1/coins/{id}/markets.
 *
 * Holds flat, commonly-used fields only; the full `quotes` map (per-currency
 * price/volume breakdown) is left as an associative array so callers can work
 * with arbitrary quote currencies without the client shipping a fixed schema.
 */
class Market
{
    /** @var string */
    private $exchangeId;

    /** @var string */
    private $exchangeName;

    /** @var string */
    private $pair;

    /** @var string */
    private $baseCurrencyId;

    /** @var string */
    private $baseCurrencyName;

    /** @var string */
    private $quoteCurrencyId;

    /** @var string */
    private $quoteCurrencyName;

    /** @var string|null */
    private $marketUrl;

    /** @var string|null */
    private $category;

    /** @var string|null */
    private $feeType;

    /** @var bool|null */
    private $outlier;

    /** @var float|null */
    private $adjustedVolume24hShare;

    /** @var string|null */
    private $trustScore;

    /** @var string|null */
    private $lastUpdated;

    /** @var array<string, array<string, float>> Raw per-currency quote map (e.g. ['USD' => ['price' => ..., 'volume_24h' => ...]]). */
    private $quotes;

    public function getExchangeId(): string { return $this->exchangeId; }
    public function setExchangeId(string $v): void { $this->exchangeId = $v; }

    public function getExchangeName(): string { return $this->exchangeName; }
    public function setExchangeName(string $v): void { $this->exchangeName = $v; }

    public function getPair(): string { return $this->pair; }
    public function setPair(string $v): void { $this->pair = $v; }

    public function getBaseCurrencyId(): string { return $this->baseCurrencyId; }
    public function setBaseCurrencyId(string $v): void { $this->baseCurrencyId = $v; }

    public function getBaseCurrencyName(): string { return $this->baseCurrencyName; }
    public function setBaseCurrencyName(string $v): void { $this->baseCurrencyName = $v; }

    public function getQuoteCurrencyId(): string { return $this->quoteCurrencyId; }
    public function setQuoteCurrencyId(string $v): void { $this->quoteCurrencyId = $v; }

    public function getQuoteCurrencyName(): string { return $this->quoteCurrencyName; }
    public function setQuoteCurrencyName(string $v): void { $this->quoteCurrencyName = $v; }

    public function getMarketUrl(): ?string { return $this->marketUrl; }
    public function setMarketUrl(?string $v): void { $this->marketUrl = $v; }

    public function getCategory(): ?string { return $this->category; }
    public function setCategory(?string $v): void { $this->category = $v; }

    public function getFeeType(): ?string { return $this->feeType; }
    public function setFeeType(?string $v): void { $this->feeType = $v; }

    public function isOutlier(): ?bool { return $this->outlier; }
    public function setOutlier(?bool $v): void { $this->outlier = $v; }

    public function getAdjustedVolume24hShare(): ?float { return $this->adjustedVolume24hShare; }
    public function setAdjustedVolume24hShare(?float $v): void { $this->adjustedVolume24hShare = $v; }

    public function getTrustScore(): ?string { return $this->trustScore; }
    public function setTrustScore(?string $v): void { $this->trustScore = $v; }

    public function getLastUpdated(): ?string { return $this->lastUpdated; }
    public function setLastUpdated(?string $v): void { $this->lastUpdated = $v; }

    public function getQuotes(): ?array { return $this->quotes; }
    public function setQuotes(?array $v): void { $this->quotes = $v; }
}
