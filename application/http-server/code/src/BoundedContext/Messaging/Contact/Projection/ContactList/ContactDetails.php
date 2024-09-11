<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Messaging\Contact\Projection\ContactList;

class ContactDetails
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $username;

    private function __construct()
    {
    }

    public function getUserId(): string
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function changeUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public static function fromUserIdAndUsername(
        string $userId,
        string $username
    ): self {
        $contactDetails = new self();
        $contactDetails->id = $userId;
        $contactDetails->username = $username;

        return $contactDetails;
    }
}
