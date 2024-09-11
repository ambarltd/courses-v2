<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Messaging\Contact\QueryHandler\ListContacts;

use Galeas\Api\Common\ExceptionBase\ProjectionCannotRead;

interface ContactListFromUserId
{
    /**
     * @throws ProjectionCannotRead
     */
    public function contactListFromUserId(string $userId): array;
}
