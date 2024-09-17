<?php

declare(strict_types=1);

namespace Galeas\Api\Common\ExceptionBase;

abstract class AccessDeniedException extends BaseException
{
    final public static function getHttpCode(): int
    {
        return 403;
    }
}
