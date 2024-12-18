<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\User\CommandHandler\VerifyPrimaryEmail;

use Galeas\Api\CommonException\InternalServerErrorException;

class AggregateIdForTakenEmailUnavailable extends InternalServerErrorException
{
    public static function getErrorIdentifier(): string
    {
        return 'Identity_User_VerifyPrimaryEmail_EmailTakenAggregateIdUnavailable';
    }
}
