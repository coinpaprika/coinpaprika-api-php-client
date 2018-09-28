<?php

namespace Coinpaprika\Model;

use Coinpaprika\Model\Traits\IdentityTrait;

/**
 * Class Ticker
 *
 * @package \Coinpaprika\Model
 *
 * @author Krzysztof Przybyszewski <kprzybyszewski@greywizard.com>
 */
class Ticker
{
    use IdentityTrait;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $symbol;

    /**
     * @var int
     */
    private $rank;

    /**
     * @var float
     */
    private $priceUSD;

    /**
     * @var float
     */
    private $priceBTC;

    /**
     * @var int
     */
    private $volume24hUSD;

    /**
     * @var int
     */
    private $marketCapUSD;

    /**
     * @var int
     */
    private $circulatingSupply;

    /**
     * @var int
     */
    private $totalSupply;

    /**
     * @var int
     */
    private $maxSupply;

    /**
     * @var float
     */
    private $percentChange1h;

    /**
     * @var float
     */
    private $percentChange24h;

    /**
     * @var float
     */
    private $percentChange7d;

    /**
     * @var int
     */
    private $lastUpdated;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getSymbol(): string
    {
        return $this->symbol;
    }

    /**
     * @param string $symbol
     */
    public function setSymbol(string $symbol): void
    {
        $this->symbol = $symbol;
    }

    /**
     * @return int
     */
    public function getRank(): int
    {
        return $this->rank;
    }

    /**
     * @param int $rank
     */
    public function setRank(int $rank): void
    {
        $this->rank = $rank;
    }

    /**
     * @return float
     */
    public function getPriceUSD(): float
    {
        return $this->priceUSD;
    }

    /**
     * @param float $priceUSD
     */
    public function setPriceUSD(float $priceUSD): void
    {
        $this->priceUSD = $priceUSD;
    }

    /**
     * @return float
     */
    public function getPriceBTC(): float
    {
        return $this->priceBTC;
    }

    /**
     * @param float $priceBTC
     */
    public function setPriceBTC(float $priceBTC): void
    {
        $this->priceBTC = $priceBTC;
    }

    /**
     * @return int
     */
    public function getVolume24hUSD(): int
    {
        return $this->volume24hUSD;
    }

    /**
     * @param int $volume24hUSD
     */
    public function setVolume24hUSD(int $volume24hUSD): void
    {
        $this->volume24hUSD = $volume24hUSD;
    }

    /**
     * @return int
     */
    public function getMarketCapUSD(): int
    {
        return $this->marketCapUSD;
    }

    /**
     * @param int $marketCapUSD
     */
    public function setMarketCapUSD(int $marketCapUSD): void
    {
        $this->marketCapUSD = $marketCapUSD;
    }

    /**
     * @return int
     */
    public function getCirculatingSupply(): int
    {
        return $this->circulatingSupply;
    }

    /**
     * @param int $circulatingSupply
     */
    public function setCirculatingSupply(int $circulatingSupply): void
    {
        $this->circulatingSupply = $circulatingSupply;
    }

    /**
     * @return int
     */
    public function getTotalSupply(): int
    {
        return $this->totalSupply;
    }

    /**
     * @param int $totalSupply
     */
    public function setTotalSupply(int $totalSupply): void
    {
        $this->totalSupply = $totalSupply;
    }

    /**
     * @return int
     */
    public function getMaxSupply(): int
    {
        return $this->maxSupply;
    }

    /**
     * @param int $maxSupply
     */
    public function setMaxSupply(int $maxSupply): void
    {
        $this->maxSupply = $maxSupply;
    }

    /**
     * @return float
     */
    public function getPercentChange1h(): float
    {
        return $this->percentChange1h;
    }

    /**
     * @param float $percentChange1h
     */
    public function setPercentChange1h(float $percentChange1h): void
    {
        $this->percentChange1h = $percentChange1h;
    }

    /**
     * @return float
     */
    public function getPercentChange24h(): float
    {
        return $this->percentChange24h;
    }

    /**
     * @param float $percentChange24h
     */
    public function setPercentChange24h(float $percentChange24h): void
    {
        $this->percentChange24h = $percentChange24h;
    }

    /**
     * @return float
     */
    public function getPercentChange7d(): float
    {
        return $this->percentChange7d;
    }

    /**
     * @param float $percentChange7d
     */
    public function setPercentChange7d(float $percentChange7d): void
    {
        $this->percentChange7d = $percentChange7d;
    }

    /**
     * @return int
     */
    public function getLastUpdated(): int
    {
        return $this->lastUpdated;
    }

    /**
     * @param int $lastUpdated
     */
    public function setLastUpdated(int $lastUpdated): void
    {
        $this->lastUpdated = $lastUpdated;
    }
}
