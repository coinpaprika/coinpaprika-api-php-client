<?php

namespace Coinpaprika\Model\KeyInfo;

class Usage
{
    /** @var string "limited plan" or "unlimited plan". */
    private $message;

    /** @var Usage\CurrentMonth */
    private $currentMonth;

    public function getMessage(): string { return $this->message; }
    public function setMessage(string $v): void { $this->message = $v; }

    public function getCurrentMonth(): Usage\CurrentMonth { return $this->currentMonth; }
    public function setCurrentMonth(Usage\CurrentMonth $v): void { $this->currentMonth = $v; }
}
