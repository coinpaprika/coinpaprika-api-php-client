<?php

namespace Coinpaprika\Model;

use Coinpaprika\Model\Traits\IdentityTrait;
use Coinpaprika\Model\Traits\NameTrait;

/**
 * Class Person
 *
 * @package \Coinpaprika\Model
 *
 * @author Krzysztof Przybyszewski <kprzybyszewski@greywizard.com>
 */
class Person
{
    use IdentityTrait, NameTrait;

    /**
     * @var int
     */
    private $teamsCount;

    /**
     * @return int
     */
    public function getTeamsCount(): int
    {
        return $this->teamsCount;
    }

    /**
     * @param int $teamsCount
     */
    public function setTeamsCount(int $teamsCount): void
    {
        $this->teamsCount = $teamsCount;
    }


}
