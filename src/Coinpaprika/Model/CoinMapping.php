<?php

namespace Coinpaprika\Model;

/**
 * Cross-provider ID mapping returned by /v1/coins/mappings.
 *
 * Shape per Coinpaprika docs:
 * https://docs.coinpaprika.com/api-reference/coins/api-id-mappings
 *
 * Business/Enterprise tier only.
 */
class CoinMapping
{
    /** @var string|null */
    private $coinpaprika;

    /** @var string|null */
    private $coinmarketcap;

    /** @var string|null */
    private $coingecko;

    /** @var string|null */
    private $cryptocompare;

    /** @var string|null International Securities Identification Number. */
    private $isin;

    /** @var string|null Digital Token Identifier per ISO standard. */
    private $dti;

    /** @var string|null RFC3999 timestamp. */
    private $updatedAt;

    public function getCoinpaprika(): ?string { return $this->coinpaprika; }
    public function setCoinpaprika(?string $v): void { $this->coinpaprika = $v; }

    public function getCoinmarketcap(): ?string { return $this->coinmarketcap; }
    public function setCoinmarketcap(?string $v): void { $this->coinmarketcap = $v; }

    public function getCoingecko(): ?string { return $this->coingecko; }
    public function setCoingecko(?string $v): void { $this->coingecko = $v; }

    public function getCryptocompare(): ?string { return $this->cryptocompare; }
    public function setCryptocompare(?string $v): void { $this->cryptocompare = $v; }

    public function getIsin(): ?string { return $this->isin; }
    public function setIsin(?string $v): void { $this->isin = $v; }

    public function getDti(): ?string { return $this->dti; }
    public function setDti(?string $v): void { $this->dti = $v; }

    public function getUpdatedAt(): ?string { return $this->updatedAt; }
    public function setUpdatedAt(?string $v): void { $this->updatedAt = $v; }
}
