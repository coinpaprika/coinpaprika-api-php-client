<?php

namespace Coinpaprika\Model;

/**
 * Single entry from /v1/changelog/ids.
 *
 * Shape per Coinpaprika docs:
 * https://docs.coinpaprika.com/api-reference/changelog/get-id-changelog-for-all-coins
 *
 * Records an ID rename: at `changedAt`, the coin identified by `currencyId`
 * had its id changed from `oldId` to `newId`. Paid plans only.
 */
class ChangelogEntry
{
    /** @var string Current coin identifier. */
    private $currencyId;

    /** @var string Previous coin identifier. */
    private $oldId;

    /** @var string New coin identifier. */
    private $newId;

    /** @var string RFC3999 timestamp. */
    private $changedAt;

    public function getCurrencyId(): string { return $this->currencyId; }
    public function setCurrencyId(string $v): void { $this->currencyId = $v; }

    public function getOldId(): string { return $this->oldId; }
    public function setOldId(string $v): void { $this->oldId = $v; }

    public function getNewId(): string { return $this->newId; }
    public function setNewId(string $v): void { $this->newId = $v; }

    public function getChangedAt(): string { return $this->changedAt; }
    public function setChangedAt(string $v): void { $this->changedAt = $v; }
}
