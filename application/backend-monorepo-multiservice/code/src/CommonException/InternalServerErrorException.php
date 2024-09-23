<?php

declare(strict_types=1);

namespace Galeas\Api\CommonException;

abstract class InternalServerErrorException extends BaseException
{
    final public static function getHttpCode(): int
    {
        return 500;
    }
}
