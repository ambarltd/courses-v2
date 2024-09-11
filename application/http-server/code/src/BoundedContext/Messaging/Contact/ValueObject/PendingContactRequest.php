<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Messaging\Contact\ValueObject;

use Galeas\Api\Common\Id\Id;

/**
 * This describes a contact with a pending contact request.
 */
class PendingContactRequest
{
    /**
     * Id of an @see \Galeas\Api\BoundedContext\Identity\User\Aggregate\User.
     *
     * @var Id
     */
    private $requesterContact;

    /**
     * Id of an @see \Galeas\Api\BoundedContext\Identity\User\Aggregate\User.
     *
     * @var Id
     */
    private $requestedContact;

    private function __construct()
    {
    }

    public function requesterContact(): Id
    {
        return $this->requesterContact;
    }

    public function requestedContact(): Id
    {
        return $this->requestedContact;
    }

    public static function fromContacts(
        Id $requesterContact,
        Id $requestedContact
    ): PendingContactRequest {
        $pendingContactRequest = new self();
        $pendingContactRequest->requesterContact = $requesterContact;
        $pendingContactRequest->requestedContact = $requestedContact;

        return $pendingContactRequest;
    }
}
