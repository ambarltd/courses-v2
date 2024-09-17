<?php

declare(strict_types=1);

namespace Galeas\Api\Common\ExceptionBase;

abstract class BaseException extends \RuntimeException
{
    /**
     * @param string $message - Message to be used as the response body
     */
    public function __construct(string $message = '')
    {
        parent::__construct($message, 0, null);
    }

    /**
     * Standardizes http code when giving exceptions back to clients.
     * Note that all exceptions should extend from this BaseException,
     * even those not going through handlers. If no httpCode is
     * appropriate, simply use @see InternalServerErrorException.
     */
    abstract public static function getHttpCode(): int;

    /**
     * Standardizes error code when giving exceptions back to clients.
     * Note that all exceptions should extend from this BaseException,
     * even those not going through handlers.
     */
    abstract public static function getErrorIdentifier(): string;
}
