<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Messaging\OneToOneConversation\CommandHandler\StartOneToOneConversation;

use Galeas\Api\Common\ExceptionBase\InternalServerErrorException;

class InvalidRFC3339String extends InternalServerErrorException
{
    public static function getErrorIdentifier(): string
    {
        return 'Messaging_OneToOneConversation_StartOneToOneConversation_InvalidRFC3339String';
    }
}
