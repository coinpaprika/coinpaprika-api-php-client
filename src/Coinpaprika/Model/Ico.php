<?php

namespace Coinpaprika\Model;

use Coinpaprika\Model\Ico\Condition;

/**
 * Class Ico
 *
 * @package \Coinpaprika\Model
 *
 * @author Krzysztof Przybyszewski <kprzybyszewski@greywizard.com>
 */
class Ico
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string|null
     */
    private $symbol;

    /**
     * @var bool
     */
    private $onMarket;

    /**
     * @var bool
     */
    private $suspended;

    /**
     * @var string
     */
    private $status;

    /**
     * @var int|null
     */
    private $goal;

    /**
     * @var int|null
     */
    private $received;

    /**
     * @var array|Condition[]|null
     */
    private $conditions;

    /**
     * @var \DateTime|null
     */
    private $startDate;

    /**
     * @var \DateTime|null
     */
    private $endDate;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string|null
     */
    public function getSymbol(): ?string
    {
        return $this->symbol;
    }

    /**
     * @param string|null $symbol
     */
    public function setSymbol(?string $symbol): void
    {
        $this->symbol = $symbol;
    }

    /**
     * @return bool
     */
    public function isOnMarket(): bool
    {
        return $this->onMarket;
    }

    /**
     * @param bool $onMarket
     */
    public function setOnMarket(bool $onMarket): void
    {
        $this->onMarket = $onMarket;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    /**
     * @return int|null
     */
    public function getGoal(): ?int
    {
        return $this->goal;
    }

    /**
     * @param int|null $goal
     */
    public function setGoal(?int $goal): void
    {
        $this->goal = $goal;
    }

    /**
     * @return int|null
     */
    public function getReceived(): ?int
    {
        return $this->received;
    }

    /**
     * @param int|null $received
     */
    public function setReceived(?int $received): void
    {
        $this->received = $received;
    }

    /**
     * @return array|Condition[]
     */
    public function getConditions(): array
    {
        if (null === $this->conditions) {
            return [];
        }

        return $this->conditions;
    }

    /**
     * @param array|Condition[]|null $conditions
     */
    public function setConditions(?array $conditions): void
    {
        $this->conditions = $conditions;
    }

    /**
     * @return \DateTime|null
     */
    public function getStartDate(): ?\DateTime
    {
        return $this->startDate;
    }

    /**
     * @param \DateTime|null $startDate
     */
    public function setStartDate(?\DateTime $startDate): void
    {
        $this->startDate = $startDate;
    }

    /**
     * @return \DateTime|null
     */
    public function getEndDate(): ?\DateTime
    {
        return $this->endDate;
    }

    /**
     * @param \DateTime|null $endDate
     */
    public function setEndDate(?\DateTime $endDate): void
    {
        $this->endDate = $endDate;
    }

    /**
     * Returns percentage value of received to goal money
     *
     * @return int
     */
    public function getReceivedPercent(): int
    {
        if (!$this->getGoal()) {
            return 0;
        }

        return (int) $this->getReceived() * 100 / $this->getGoal();
    }

    /**
     * @return bool
     */
    public function isSuspended(): bool
    {
        return $this->suspended;
    }

    /**
     * @param bool $suspended
     */
    public function setSuspended(bool $suspended): void
    {
        $this->suspended = $suspended;
    }
}
