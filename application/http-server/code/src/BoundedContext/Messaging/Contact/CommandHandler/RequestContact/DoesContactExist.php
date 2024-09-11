<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Messaging\Contact\CommandHandler\RequestContact;

use Galeas\Api\Common\ExceptionBase\ProjectionCannotRead;

interface DoesContactExist
{
    /**
     * @throws ProjectionCannotRead
     */
    public function doesContactExist(string $firstContact, string $secondContact): bool;
}
