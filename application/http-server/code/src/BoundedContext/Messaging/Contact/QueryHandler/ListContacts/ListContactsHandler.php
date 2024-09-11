<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Messaging\Contact\QueryHandler\ListContacts;

use Galeas\Api\BoundedContext\Messaging\Contact\Query\ListContacts;
use Galeas\Api\Common\ExceptionBase\ProjectionCannotRead;

class ListContactsHandler
{
    /**
     * @var ContactListFromUserId
     */
    private $contactListFromUserId;

    public function __construct(ContactListFromUserId $contactListFromUserId)
    {
        $this->contactListFromUserId = $contactListFromUserId;
    }

    /**
     * @throws ProjectionCannotRead
     */
    public function handle(ListContacts $command): array
    {
        return $this
            ->contactListFromUserId
            ->contactListFromUserId(
                $command->authorizerId
            );
    }
}
