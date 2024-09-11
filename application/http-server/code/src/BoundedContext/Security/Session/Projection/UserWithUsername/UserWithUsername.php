<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Security\Session\Projection\UserWithUsername;

class UserWithUsername
{
    /**
     * @var string
     */
    private $canonicalUsername;

    /**
     * @var string
     */
    private $id;

    private function __construct()
    {
    }

    public function getCanonicalUsername(): string
    {
        return $this->canonicalUsername;
    }

    public function getUserId(): string
    {
        return $this->id;
    }

    public function changeUsername(string $username): self
    {
        $this->canonicalUsername = strtolower($username);

        return $this;
    }

    public static function fromProperties(
        string $username,
        string $userId
    ): self {
        $hashedPasswordObject = new self();
        $hashedPasswordObject->canonicalUsername = strtolower($username);
        $hashedPasswordObject->id = $userId;

        return $hashedPasswordObject;
    }
}
