<?php

declare(strict_types=1);

namespace Galeas\Api\Common\ExceptionBase;

abstract class BadRequestException extends BaseException
{
    /**
     * {@inheritdoc}
     */
    final public static function getHttpCode(): int
    {
        return 400;
    }
}
