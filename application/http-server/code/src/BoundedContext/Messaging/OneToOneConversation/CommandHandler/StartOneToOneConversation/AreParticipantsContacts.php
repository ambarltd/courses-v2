<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Messaging\OneToOneConversation\CommandHandler\StartOneToOneConversation;

use Galeas\Api\Common\ExceptionBase\ProjectionCannotRead;

interface AreParticipantsContacts
{
    /**
     * @throws ProjectionCannotRead
     */
    public function areParticipantsContacts(string $participant1, string $participant2): bool;
}
