<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\User\Projection\TakenUsername;

class TakenUsername
{
    private string $id;

    private string $lowercaseUsername;

    private bool $verifiedPrimaryEmail;

    private function __construct() {}

    public function verify(): self
    {
        $this->verifiedPrimaryEmail = true;

        return $this;
    }

    public static function fromUserIdAndUsername(string $userId, string $username, bool $verifiedPrimaryEmail): self
    {
        $takenUsername = new self();
        $takenUsername->id = $userId;
        $takenUsername->lowercaseUsername = strtolower($username);
        $takenUsername->verifiedPrimaryEmail = $verifiedPrimaryEmail;

        return $takenUsername;
    }
}
