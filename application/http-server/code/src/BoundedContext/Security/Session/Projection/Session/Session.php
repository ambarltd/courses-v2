<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Security\Session\Projection\Session;

class Session
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string|null
     */
    private $userId;

    /**
     * @var string
     */
    private $sessionToken;

    /**
     * @var bool
     */
    private $signedOut;

    /**
     * @var \DateTimeImmutable
     */
    private $tokenLastRefreshedAt;

    private function __construct()
    {
    }

    public function getSessionId(): string
    {
        return $this->id;
    }

    public function getUserId(): ?string
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
        ?string $userId,
        string $sessionToken,
        bool $signedOut,
        \DateTimeImmutable $tokenLastRefreshedAt
    ): self {
        $this->userId = $userId;
        $this->sessionToken = $sessionToken;
        $this->signedOut = $signedOut;
        $this->tokenLastRefreshedAt = $tokenLastRefreshedAt;

        return $this;
    }

    public static function fromProperties(
        string $sessionId,
        ?string $userId,
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
