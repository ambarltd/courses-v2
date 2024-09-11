<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Messaging\OneToOneConversation\Projection\ContactPair;

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

    /**
     * @var bool
     */
    private $active;

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

    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @return ContactPair
     */
    public function changeProperties(
        string $contactId,
        string $firstContactId,
        string $secondContactId,
        bool $active
    ): self {
        $this->id = $contactId;
        $this->firstContactId = $firstContactId;
        $this->secondContactId = $secondContactId;
        $this->active = $active;

        return $this;
    }

    /**
     * @return ContactPair
     */
    public static function fromProperties(
        string $contactId,
        string $firstContactId,
        string $secondContactId,
        bool $active
    ): self {
        $contactPair = new self();
        $contactPair->id = $contactId;
        $contactPair->firstContactId = $firstContactId;
        $contactPair->secondContactId = $secondContactId;
        $contactPair->active = $active;

        return $contactPair;
    }
}
