<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\User\Projection\TakenUsername;

class TakenUsername
{
    private string $id;

    private string $canonicalUsername;

    private function __construct() {}

    public function getUserId(): string
    {
        return $this->id;
    }

    public function getCanonicalUsername(): string
    {
        return $this->canonicalUsername;
    }

    /**
     * @return $this
     */
    public function changeUsername(string $username)
    {
        $this->canonicalUsername = strtolower($username);

        return $this;
    }

    public static function fromUserIdAndUsername(string $userId, string $username): self
    {
        $takenUsername = new self();
        $takenUsername->id = $userId;
        $takenUsername->canonicalUsername = strtolower($username);

        return $takenUsername;
    }
}
