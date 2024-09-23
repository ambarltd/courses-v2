<?php

declare(strict_types=1);

namespace Galeas\Api\CommonException;

abstract class UnauthorizedException extends BaseException
{
    final public static function getHttpCode(): int
    {
        return 401;
    }
}
