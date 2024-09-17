<?php

declare(strict_types=1);

namespace Galeas\Api\Common\ExceptionBase;

class ReactionCannotProcess extends DatabaseFailure
{
    final public static function getErrorIdentifier(): string
    {
        return 'Common_ReactionCannotProcess';
    }
}
