<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Messaging\Contact\Projection\ContactList;

class PendingContact
{
    /**
     * @var string
     */
    private $requesterContactId;

    /**
     * @var string
     */
    private $requestedContactId;

    private function __construct()
    {
    }

    public function getRequesterContactId(): string
    {
        return $this->requesterContactId;
    }

    public function getRequestedContactId(): string
    {
        return $this->requestedContactId;
    }

    public static function fromContacts(
        string $requestedContactId,
        string $requesterContactId
    ): self {
        $pendingContact = new self();
        $pendingContact->requestedContactId = $requestedContactId;
        $pendingContact->requesterContactId = $requesterContactId;

        return $pendingContact;
    }
}
