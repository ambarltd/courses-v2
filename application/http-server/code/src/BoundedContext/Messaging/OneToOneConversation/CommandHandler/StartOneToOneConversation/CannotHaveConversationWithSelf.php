<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Messaging\OneToOneConversation\CommandHandler\StartOneToOneConversation;

use Galeas\Api\Common\ExceptionBase\BadRequestException;

class CannotHaveConversationWithSelf extends BadRequestException
{
    public static function getErrorIdentifier(): string
    {
        return 'Messaging_OneToOneConversation_StartOneToOneConversation_CannotHaveConversationWithSelf';
    }
}
