<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\AuthenticationAllServices\Projection\Session;

class Session
{
    private string $id;

    private string $userId;

    private string $sessionToken;

    private bool $signedOut;

    private \DateTimeImmutable $tokenLastRefreshedAt;

    private function __construct() {}

    public function getSessionId(): string
    {
        return $this->id;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getSessionToken(): string
    {
        return $this->sessionToken;
    }

    public function isSignedOut(): bool
    {
        return $this->signedOut;
    }

    public function getTokenLastRefreshedAt(): \DateTimeImmutable
    {
        return $this->tokenLastRefreshedAt;
    }

    public function changeProperties(
        string $sessionToken,
        bool $signedOut,
        \DateTimeImmutable $tokenLastRefreshedAt
    ): self {
        $this->sessionToken = $sessionToken;
        $this->signedOut = $signedOut;
        $this->tokenLastRefreshedAt = $tokenLastRefreshedAt;

        return $this;
    }

    public static function fromProperties(
        string $sessionId,
        string $userId,
        string $sessionToken,
        bool $signedOut,
        \DateTimeImmutable $tokenLastRefreshedAt
    ): self {
        $session = new self();
        $session->id = $sessionId;
        $session->userId = $userId;
        $session->sessionToken = $sessionToken;
        $session->signedOut = $signedOut;
        $session->tokenLastRefreshedAt = $tokenLastRefreshedAt;

        return $session;
    }
}
