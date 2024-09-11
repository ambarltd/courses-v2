<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Messaging\Contact\CommandHandler\RequestContact;

use Galeas\Api\Common\ExceptionBase\ProjectionCannotRead;

interface DoesRequestedContactExist
{
    /**
     * @throws ProjectionCannotRead
     */
    public function doesRequestedContactExist(string $requestedContact): bool;
}
