<?php

namespace Coinpaprika\Model;


/**
 * Class HistoricalTicker
 *
 * @package \Coinpaprika\Model
 *
 */
class HistoricalTicker
{
    /**
     * @var float
     */
    private $price;

    /**
     * @var int
     */
    private $volume24h;

    /**
     * @var int
     */
    private $marketCap;

    /**
     * @var string
     */
    private $timestamp;

    /**
     * @return float
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * @param float $price
     */
    public function setPrice(float $price): void
    {
        $this->price = $price;
    }

    /**
     * @return int
     */
    public function getVolume24h(): int
    {
        return $this->volume24h;
    }

    /**
     * @param int $volume24h
     */
    public function setVolume24h(int $volume24h): void
    {
        $this->volume24h = $volume24h;
    }

    /**
     * @return int
     */
    public function getMarketCap(): int
    {
        return $this->marketCap;
    }

    /**
     * @param int $marketCap
     */
    public function setMarketCap(int $marketCap): void
    {
        $this->marketCap = $marketCap;
    }

    /**
     * @return string
     */
    public function getTimestamp(): string
    {
        return $this->timestamp;
    }

    /**
     * @param string $timestamp
     */
    public function setTimestamp(string $timestamp): void
    {
        $this->timestamp = $timestamp;
    }
}
