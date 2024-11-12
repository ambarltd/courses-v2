<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Security\Session\Projection\UserWithUsername;

class UserWithUsername
{
    private string $id;

    private string $lowercaseUsername;

    private bool $verified;

    private function __construct() {}

    public function getUserId(): string
    {
        return $this->id;
    }

    public function verify(): self
    {
        $this->verified = true;

        return $this;
    }

    public static function fromProperties(
        string $username,
        string $userId,
        bool $verified
    ): self {
        $userWithUsername = new self();
        $userWithUsername->lowercaseUsername = strtolower($username);
        $userWithUsername->id = $userId;
        $userWithUsername->verified = $verified;

        return $userWithUsername;
    }
}
