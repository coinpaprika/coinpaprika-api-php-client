<?php

namespace Coinpaprika\Model;

use Coinpaprika\Model\Traits\IdentityTrait;
use Coinpaprika\Model\Traits\NameTrait;

/**
 * Class Tag
 *
 * @package \Coinpaprika\Model
 *
 * @author Krzysztof Przybyszewski <kprzybyszewski@greywizard.com>
 */
class Tag
{
    use IdentityTrait, NameTrait;

    /**
     * @var int
     */
    private $coinCounter;

    /**
     * @var int
     */
    private $icoCounter;

    /**
     * @return int
     */
    public function getCoinCounter(): int
    {
        return $this->coinCounter;
    }

    /**
     * @param int $coinCounter
     */
    public function setCoinCounter(int $coinCounter): void
    {
        $this->coinCounter = $coinCounter;
    }

    /**
     * @return int
     */
    public function getIcoCounter(): int
    {
        return $this->icoCounter;
    }

    /**
     * @param int $icoCounter
     */
    public function setIcoCounter(int $icoCounter): void
    {
        $this->icoCounter = $icoCounter;
    }
}
