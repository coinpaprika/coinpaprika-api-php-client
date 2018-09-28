<?php

namespace Coinpaprika\Exception;

/**
 * Class InvalidResponseException
 *
 * @codeCoverageIgnore
 *
 * @package \Coinpaprika\Exception
 *
 * @author Krzysztof Przybyszewski <kprzybyszewski@greywizard.com>
 */
class InvalidResponseException extends \Exception
{
    public function __construct(string $message = "", int $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
