<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Messaging\Contact\CommandHandler\AcceptContactRequest;

use Galeas\Api\Common\ExceptionBase\ProjectionCannotRead;

interface ContactIdFromContacts
{
    /**
     * @throws ProjectionCannotRead
     */
    public function contactIdFromContacts(string $firstContact, string $secondContact): ?string;
}
