<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Security\Session\Projection\Session;

class Session
{
    private string $id;

    private string $sessionToken;

    private function __construct() {}

    public function getSessionId(): string
    {
        return $this->id;
    }

    public function getSessionToken(): string
    {
        return $this->sessionToken;
    }

    public function refreshToken(
        string $sessionToken,
    ): self {
        $this->sessionToken = $sessionToken;

        return $this;
    }

    public static function fromProperties(
        string $sessionId,
        string $sessionToken,
    ): self {
        $session = new self();
        $session->id = $sessionId;
        $session->sessionToken = $sessionToken;

        return $session;
    }
}
