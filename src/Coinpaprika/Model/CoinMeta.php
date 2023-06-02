<?php

namespace Coinpaprika\Model;

use Coinpaprika\Model\Traits\IdentityTrait;
use Coinpaprika\Model\Traits\NameTrait;

/**
 * Class Coin
 *
 * @package \Coinpaprika\Model
 *
 * @author Krzysztof Przybyszewski <kprzybyszewski@greywizard.com>
 */
class CoinMeta
{
    use IdentityTrait, NameTrait;

    /**
     * @var string
     */
    private $symbol;

    /**
     * @var int
     */
    private $rank;

    /**
     * @var boolean
     */
    private $new;

    /**
     * @var boolean
     */
    private $active;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $logo;

    /**
     * @var Tag[]
     */
    private $tags;

    /**
     * @var TeamMember[]
     */
    private $teamMembers;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $message;

    /**
     * @var bool
     */
    private $openSource;

    /**
     * @var string
     */
    private $startedAt;

    /**
     * @var string
     */
    private $developmentStatus;

    /**
     * @var bool
     */
    private $hardwareWallet;

    /**
     * @var string
     */
    private $proofType;

    /**
     * @var string
     */
    private $orgStructure;

    /**
     * @var string
     */
    private $hashAlgorithm;

    /**
     * @var string
     */
    private $firstDataAt;

    /**
     * @var string
     */
    private $lastDataAt;

    /**
     * @return string
     */
    public function getSymbol(): string
    {
        return $this->symbol;
    }

    /**
     * @param string $symbol
     */
    public function setSymbol(string $symbol): void
    {
        $this->symbol = $symbol;
    }

    /**
     * @return int
     */
    public function getRank(): int
    {
        return $this->rank;
    }

    /**
     * @param int $rank
     */
    public function setRank(int $rank): void
    {
        $this->rank = $rank;
    }

    /**
     * @return bool
     */
    public function isNew(): bool
    {
        return $this->new;
    }

    /**
     * @param bool $new
     */
    public function setNew(bool $new): void
    {
        $this->new = $new;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getLogo(): string
    {
        return $this->type;
    }

    /**
     * @param string $logo
     */
    public function setLogo(string $logo): void
    {
        $this->logo = $logo;
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

    /**
     * @return TeamMember[]
     */
    public function getTeam(): array
    {
        return $this->teamMembers;
    }

    /**
     * @param TeamMember[] $teamMembers
     */
    public function setTeam(array $teamMembers): void
    {
        $this->teamMembers = $teamMembers;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    /**
     * @return bool
     */
    public function isOpenSource(): bool
    {
        return $this->openSource;
    }

    /**
     * @param bool $openSource
     */
    public function setOpenSource(bool $openSource): void
    {
        $this->openSource = $openSource;
    }

    /**
     * @return string
     */
    public function getStartedAt(): string
    {
        return $this->startedAt;
    }

    /**
     * @param string $startedAt
     */
    public function setStartedAt(string $startedAt): void
    {
        $this->startedAt = $startedAt;
    }

    /**
     * @return string
     */
    public function getDevelopmentStatus(): string
    {
        return $this->developmentStatus;
    }

    /**
     * @param string $developmentStatus
     */
    public function setDevelopmentStatus(string $developmentStatus): void
    {
        $this->developmentStatus = $developmentStatus;
    }

    /**
     * @return bool
     */
    public function isHardwareWallet(): bool
    {
        return $this->hardwareWallet;
    }

    /**
     * @param bool $hardwareWallet
     */
    public function setHardwareWallet(bool $hardwareWallet): void
    {
        $this->hardwareWallet = $hardwareWallet;
    }

    /**
     * @return string
     */
    public function getProofType(): string
    {
        return $this->proofType;
    }

    /**
     * @param string $proofType
     */
    public function setProofType(string $proofType): void
    {
        $this->proofType = $proofType;
    }

    /**
     * @return string
     */
    public function getOrgStructure(): string
    {
        return $this->orgStructure;
    }

    /**
     * @param string $orgStructure
     */
    public function setOrgStructure(string $orgStructure): void
    {
        $this->orgStructure = $orgStructure;
    }

    /**
     * @return string
     */
    public function getHashAlgorithm(): string
    {
        return $this->hashAlgorithm;
    }

    /**
     * @param string $hashAlgorithm
     */
    public function setHashAlgorithm(string $hashAlgorithm): void
    {
        $this->hashAlgorithm = $hashAlgorithm;
    }

    /**
     * @return string
     */
    public function getFirstDataAt(): string
    {
        return $this->firstDataAt;
    }

    /**
     * @param string $firstDataAt
     */
    public function setFirstDataAt(string $firstDataAt): void
    {
        $this->firstDataAt = $firstDataAt;
    }

    /**
     * @return string
     */
    public function getLastDataAt(): string
    {
        return $this->lastDataAt;
    }

    /**
     * @param string $lastDataAt
     */
    public function setLastDataAt(string $lastDataAt): void
    {
        $this->lastDataAt = $lastDataAt;
    }
}
