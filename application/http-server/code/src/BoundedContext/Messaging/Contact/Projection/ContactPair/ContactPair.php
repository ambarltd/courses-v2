<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Messaging\Contact\Projection\ContactPair;

class ContactPair
{
    /**
     * @var string
     */
    private $id;

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

    public function getContactId(): string
    {
        return $this->id;
    }

    public function getFirstContactId(): string
    {
        return $this->firstContactId;
    }

    public function getSecondContactId(): string
    {
        return $this->secondContactId;
    }

    /**
     * @return ContactPair
     */
    public function changeProperties(
        string $contactId,
        string $firstContactId,
        string $secondContactId
    ): self {
        $this->id = $contactId;
        $this->firstContactId = $firstContactId;
        $this->secondContactId = $secondContactId;

        return $this;
    }

    /**
     * @return ContactPair
     */
    public static function fromProperties(
        string $contactId,
        string $firstContactId,
        string $secondContactId
    ): self {
        $contactPair = new self();
        $contactPair->id = $contactId;
        $contactPair->firstContactId = $firstContactId;
        $contactPair->secondContactId = $secondContactId;

        return $contactPair;
    }
}
