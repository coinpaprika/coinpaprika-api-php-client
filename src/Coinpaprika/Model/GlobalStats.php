<?php

namespace Coinpaprika\Model;

/**
 * Class GlobalStats
 *
 * @package \Coinpaprika\Model
 *
 * @author Krzysztof Przybyszewski <kprzybyszewski@greywizard.com>
 */
class GlobalStats
{
    /**
     * @var int
     */
    private $marketCapUsd;

    /**
     * @var int
     */
    private $volume24hUSD;

    /**
     * @var float
     */
    private $bitcoinDominancePercentage;

    /**
     * @var int
     */
    private $cryptocurrenciesNumber;

    /**
     * @var int
     */
    private $lastUpdated;

    /**
     * @return int
     */
    public function getMarketCapUsd(): int
    {
        return $this->marketCapUsd;
    }

    /**
     * @param int $marketCapUsd
     */
    public function setMarketCapUsd(int $marketCapUsd): void
    {
        $this->marketCapUsd = $marketCapUsd;
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
     * @return float
     */
    public function getBitcoinDominancePercentage(): float
    {
        return $this->bitcoinDominancePercentage;
    }

    /**
     * @param float $bitcoinDominancePercentage
     */
    public function setBitcoinDominancePercentage(float $bitcoinDominancePercentage): void
    {
        $this->bitcoinDominancePercentage = $bitcoinDominancePercentage;
    }

    /**
     * @return int
     */
    public function getCryptocurrenciesNumber(): int
    {
        return $this->cryptocurrenciesNumber;
    }

    /**
     * @param int $cryptocurrenciesNumber
     */
    public function setCryptocurrenciesNumber(int $cryptocurrenciesNumber): void
    {
        $this->cryptocurrenciesNumber = $cryptocurrenciesNumber;
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
