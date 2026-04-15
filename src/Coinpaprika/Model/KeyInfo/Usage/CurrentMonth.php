<?php

namespace Coinpaprika\Model\KeyInfo\Usage;

/**
 * Request counters for the current billing period.
 *
 * Both values are `-1` on unlimited plans per Coinpaprika docs.
 */
class CurrentMonth
{
    /** @var int Requests used this month, or -1 for unlimited plans. */
    private $requestsMade;

    /** @var int Remaining requests this month, or -1 for unlimited plans. */
    private $requestsLeft;

    public function getRequestsMade(): int { return $this->requestsMade; }
    public function setRequestsMade(int $v): void { $this->requestsMade = $v; }

    public function getRequestsLeft(): int { return $this->requestsLeft; }
    public function setRequestsLeft(int $v): void { $this->requestsLeft = $v; }
}
