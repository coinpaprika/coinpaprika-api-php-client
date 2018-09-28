<?php

namespace Coinpaprika\Model;

use Coinpaprika\Model\Coin;
use Coinpaprika\Model\Exchange;
use Coinpaprika\Model\Ico;
use Coinpaprika\Model\Person;
use Coinpaprika\Model\Tag;

/**
 * Class Search
 *
 * @package \Coinpaprika\Model
 *
 * @author Krzysztof Przybyszewski <kprzybyszewski@greywizard.com>
 */
class Search
{
    /**
     * @var Coin[]
     */
    private $currencies = [];

    /**
     * @var Exchange[]
     */
    private $exchanges = [];

    /**
     * @var Ico[]
     */
    private $icos = [];

    /**
     * @var Person[]
     */
    private $people = [];

    /**
     * @var Tag[]
     */
    private $tags = [];

    /**
     * @return Coin[]
     */
    public function getCurrencies(): array
    {
        return $this->currencies;
    }

    /**
     * @param Coin[] $currencies
     */
    public function setCurrencies(array $currencies): void
    {
        $this->currencies = $currencies;
    }

    /**
     * @return Exchange[]
     */
    public function getExchanges(): array
    {
        return $this->exchanges;
    }

    /**
     * @param Exchange[] $exchanges
     */
    public function setExchanges(array $exchanges): void
    {
        $this->exchanges = $exchanges;
    }

    /**
     * @return Ico[]
     */
    public function getIcos(): array
    {
        return $this->icos;
    }

    /**
     * @param Ico[] $icos
     */
    public function setIcos(array $icos): void
    {
        $this->icos = $icos;
    }

    /**
     * @return Person[]
     */
    public function getPeople(): array
    {
        return $this->people;
    }

    /**
     * @param Person[] $people
     */
    public function setPeople(array $people): void
    {
        $this->people = $people;
    }

    /**
     * @return Tag[]
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * @param Tag[] $tags
     */
    public function setTags(array $tags): void
    {
        $this->tags = $tags;
    }
}
