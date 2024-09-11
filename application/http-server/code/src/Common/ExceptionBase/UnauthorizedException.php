<?php

declare(strict_types=1);

namespace Galeas\Api\Common\ExceptionBase;

abstract class UnauthorizedException extends BaseException
{
    /**
     * {@inheritdoc}
     */
    final public static function getHttpCode(): int
    {
        return 401;
    }
}
