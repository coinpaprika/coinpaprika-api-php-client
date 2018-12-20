<?php

namespace Coinpaprika\Model\Ico;

/**
 * Class Condition
 *
 * @package \Coinpaprika\Model\Ico
 *
 * @author Krzysztof Przybyszewski <kprzybyszewski@greywizard.com>
 */
class Condition
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var bool
     */
    private $value;

    /**
     * @var string
     */
    private $comment;

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
     * @return bool
     */
    public function isValue(): bool
    {
        return $this->value;
    }

    /**
     * @param bool $value
     */
    public function setValue(bool $value): void
    {
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getComment(): string
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     */
    public function setComment(string $comment): void
    {
        $this->comment = $comment;
    }
}
