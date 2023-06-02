<?php

namespace Coinpaprika\Model;

use Coinpaprika\Model\Traits\IdentityTrait;
use Coinpaprika\Model\Traits\NameTrait;

/**
 * Class TeamMember
 *
 * @package \Coinpaprika\Model
 *
 */
class TeamMember
{
    use IdentityTrait, NameTrait;

    /**
     * @var string
     */
    private $position;

    /**
     * @return string
     */
    public function getPosition(): string
    {
        return $this->position;
    }

    /**
     * @param string $position
     */
    public function setPosition(string $position): void
    {
        $this->position = $position;
    }


}
