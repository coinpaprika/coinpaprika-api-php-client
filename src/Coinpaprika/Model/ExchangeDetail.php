<?php

namespace Coinpaprika\Model;

use Coinpaprika\Model\Traits\IdentityTrait;
use Coinpaprika\Model\Traits\NameTrait;

/**
 * Full exchange response returned by /v1/exchanges and /v1/exchanges/{id}.
 *
 * Separate from {@see Exchange} (which is used inside search results and only
 * carries id/name/rank) so that extending shape here doesn't ripple into the
 * Search model.
 *
 * Only flat, commonly-queried fields are typed. Nested structures (links,
 * fiats, quotes) are kept as arrays for caller flexibility.
 */
class ExchangeDetail
{
    use IdentityTrait, NameTrait;

    /** @var string[]|null */
    private $type;

    /** @var string|null */
    private $description;

    /** @var bool|null */
    private $active;

    /** @var bool|null */
    private $websiteStatus;

    /** @var bool|null */
    private $apiStatus;

    /** @var string|null */
    private $message;

    /** @var array|null */
    private $links;

    /** @var array|null */
    private $fiats;

    /** @var array|null */
    private $quotes;

    /** @var bool|null */
    private $marketsDataFetched;

    /** @var int|null */
    private $adjustedRank;

    /** @var int|null */
    private $reportedRank;

    /** @var int|null */
    private $currencies;

    /** @var int|null */
    private $markets;

    /** @var int|null */
    private $sessionsPerMonth;

    /** @var float|null */
    private $confidenceScore;

    /** @var string|null */
    private $lastUpdated;

    public function getType(): ?array { return $this->type; }
    public function setType(?array $v): void { $this->type = $v; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $v): void { $this->description = $v; }

    public function isActive(): ?bool { return $this->active; }
    public function setActive(?bool $v): void { $this->active = $v; }

    public function isWebsiteStatus(): ?bool { return $this->websiteStatus; }
    public function setWebsiteStatus(?bool $v): void { $this->websiteStatus = $v; }

    public function isApiStatus(): ?bool { return $this->apiStatus; }
    public function setApiStatus(?bool $v): void { $this->apiStatus = $v; }

    public function getMessage(): ?string { return $this->message; }
    public function setMessage(?string $v): void { $this->message = $v; }

    public function getLinks(): ?array { return $this->links; }
    public function setLinks(?array $v): void { $this->links = $v; }

    public function getFiats(): ?array { return $this->fiats; }
    public function setFiats(?array $v): void { $this->fiats = $v; }

    public function getQuotes(): ?array { return $this->quotes; }
    public function setQuotes(?array $v): void { $this->quotes = $v; }

    public function isMarketsDataFetched(): ?bool { return $this->marketsDataFetched; }
    public function setMarketsDataFetched(?bool $v): void { $this->marketsDataFetched = $v; }

    public function getAdjustedRank(): ?int { return $this->adjustedRank; }
    public function setAdjustedRank(?int $v): void { $this->adjustedRank = $v; }

    public function getReportedRank(): ?int { return $this->reportedRank; }
    public function setReportedRank(?int $v): void { $this->reportedRank = $v; }

    public function getCurrencies(): ?int { return $this->currencies; }
    public function setCurrencies(?int $v): void { $this->currencies = $v; }

    public function getMarkets(): ?int { return $this->markets; }
    public function setMarkets(?int $v): void { $this->markets = $v; }

    public function getSessionsPerMonth(): ?int { return $this->sessionsPerMonth; }
    public function setSessionsPerMonth(?int $v): void { $this->sessionsPerMonth = $v; }

    public function getConfidenceScore(): ?float { return $this->confidenceScore; }
    public function setConfidenceScore(?float $v): void { $this->confidenceScore = $v; }

    public function getLastUpdated(): ?string { return $this->lastUpdated; }
    public function setLastUpdated(?string $v): void { $this->lastUpdated = $v; }
}
