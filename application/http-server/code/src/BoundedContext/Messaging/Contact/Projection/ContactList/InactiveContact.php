<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Messaging\Contact\Projection\ContactList;

class InactiveContact
{
    /**
     * @var string
     */
    private $firstContactId;

    /**
     * @var string
     */
    private $secondContactId;

    private function __construct()
    {
    }

    public function getFirstContactId(): string
    {
        return $this->firstContactId;
    }

    public function getSecondContactId(): string
    {
        return $this->secondContactId;
    }

    public static function fromContacts(
        string $firstContactId,
        string $secondContactId
    ): self {
        $inactiveContact = new self();
        $inactiveContact->firstContactId = $firstContactId;
        $inactiveContact->secondContactId = $secondContactId;

        return $inactiveContact;
    }
}
