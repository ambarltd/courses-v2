<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\User\CommandHandler\VerifyPrimaryEmail;

use Galeas\Api\CommonException\NotFoundException;

class NoVerifiableUserFoundForCode extends NotFoundException
{
    public static function getErrorIdentifier(): string
    {
        return 'Identity_User_VerifyPrimaryEmail_NoVerifiableUserFoundForCode';
    }
}
