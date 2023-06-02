<?php

namespace Coinpaprika\Model;

/**
 * Class OHLCV
 *
 * @package \Coinpaprika\Model
 *
 */
class OHLCV
{
    /**
     * @var float
     */
    private $open;

    /**
     * @var float
     */
    private $high;

    /**
     * @var float
     */
    private $low;

    /**
     * @var float
     */
    private $close;

    /**
     * @var int
     */
    private $volume;

    /**
     * @var int
     */
    private $marketCap;

    /**
     * @var string
     */
    private $timeOpen;

    /**
     * @var string
     */
    private $timeClose;

    /**
     * @return float
     */
    public function getOpen(): float
    {
        return $this->open;
    }

    /**
     * @param float $open
     */
    public function setOpen(float $open): void
    {
        $this->open = $open;
    }

    /**
     * @return float
     */
    public function getHigh(): float
    {
        return $this->high;
    }

    /**
     * @param float $high
     */
    public function setHigh(float $high): void
    {
        $this->high = $high;
    }

    /**
     * @return float
     */
    public function getLow(): float
    {
        return $this->low;
    }

    /**
     * @param float $low
     */
    public function setLow(float $low): void
    {
        $this->low = $low;
    }

    /**
     * @return float
     */
    public function getClose(): float
    {
        return $this->close;
    }

    /**
     * @param float $close
     */
    public function setClose(float $close): void
    {
        $this->close = $close;
    }

    /**
     * @return int
     */
    public function getVolume(): int
    {
        return $this->volume;
    }

    /**
     * @param int $volume
     */
    public function setVolume(int $volume): void
    {
        $this->volume = $volume;
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
    public function getTimeOpen(): string
    {
        return $this->timeOpen;
    }

    /**
     * @param string $timeOpen
     */
    public function setTimeOpen(string $timeOpen): void
    {
        $this->timeOpen = $timeOpen;
    }

    /**
     * @return string
     */
    public function getTimeClose(): string
    {
        return $this->timeClose;
    }

    /**
     * @param string $timeClose
     */
    public function setTimeClose(string $timeClose): void
    {
        $this->timeClose = $timeClose;
    }
}
