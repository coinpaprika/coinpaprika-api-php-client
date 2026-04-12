<?php

namespace Coinpaprika\Model;

/**
 * Response model for /v1/key/info.
 *
 * Shape per Coinpaprika docs:
 * https://docs.coinpaprika.com/api-reference/key/get-api-key-info
 *
 * On unlimited plans, {@see KeyInfo\Usage\CurrentMonth::getRequestsMade()} and
 * {@see KeyInfo\Usage\CurrentMonth::getRequestsLeft()} return `-1`.
 */
class KeyInfo
{
    /** @var string */
    private $plan;

    /** @var string */
    private $planStartedAt;

    /** @var string */
    private $planStatus;

    /** @var string */
    private $portalUrl;

    /** @var KeyInfo\Usage */
    private $usage;

    public function getPlan(): string { return $this->plan; }
    public function setPlan(string $v): void { $this->plan = $v; }

    public function getPlanStartedAt(): string { return $this->planStartedAt; }
    public function setPlanStartedAt(string $v): void { $this->planStartedAt = $v; }

    public function getPlanStatus(): string { return $this->planStatus; }
    public function setPlanStatus(string $v): void { $this->planStatus = $v; }

    public function getPortalUrl(): string { return $this->portalUrl; }
    public function setPortalUrl(string $v): void { $this->portalUrl = $v; }

    public function getUsage(): KeyInfo\Usage { return $this->usage; }
    public function setUsage(KeyInfo\Usage $v): void { $this->usage = $v; }
}
