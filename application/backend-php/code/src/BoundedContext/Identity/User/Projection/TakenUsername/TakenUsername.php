<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\User\Projection\TakenUsername;

class TakenUsername
{
    private string $id;

    private string $lowercaseUsername;

    private function __construct() {}

    public static function fromUserIdAndUsername(string $userId, string $username): self
    {
        $takenUsername = new self();
        $takenUsername->id = $userId;
        $takenUsername->lowercaseUsername = strtolower($username);

        return $takenUsername;
    }
}
