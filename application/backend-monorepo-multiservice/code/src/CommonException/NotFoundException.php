<?php

declare(strict_types=1);

namespace Galeas\Api\CommonException;

abstract class NotFoundException extends BaseException
{
    final public static function getHttpCode(): int
    {
        return 404;
    }
}
