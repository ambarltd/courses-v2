<?php

declare(strict_types=1);

namespace Galeas\Api\Common\ExceptionBase;

abstract class InternalServerErrorException extends BaseException
{
    /**
     * {@inheritdoc}
     */
    final public static function getHttpCode(): int
    {
        return 500;
    }
}
