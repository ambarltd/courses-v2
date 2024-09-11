<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Messaging\Contact\Projection\ExistingUser;

class ExistingUser
{
    /**
     * @var string
     */
    private $id;

    private function __construct()
    {
    }

    public function getUserId(): string
    {
        return $this->id;
    }

    public static function fromUserId(
        string $userId
    ): self {
        $existingUser = new self();
        $existingUser->id = $userId;

        return $existingUser;
    }
}
