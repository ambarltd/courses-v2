<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Security\Session\Event;

use Galeas\Api\BoundedContext\Security\Session\Aggregate\Session;
use Galeas\Api\BoundedContext\Security\Session\ValueObject\SessionDetails;
use Galeas\Api\Common\Event\EventTrait;
use Galeas\Api\Common\Id\Id;

class TokenRefreshed implements EventTransformedSession
{
    use EventTrait;

    private string $withIp;

    private string $withExistingSessionToken;

    private string $refreshedSessionToken;

    public function withIp(): string
    {
        return $this->withIp;
    }

    public function withExistingSessionToken(): string
    {
        return $this->withExistingSessionToken;
    }

    public function refreshedSessionToken(): string
    {
        return $this->refreshedSessionToken;
    }

    public static function new(
        Id $eventId,
        Id $aggregateId,
        int $aggregateVersion,
        Id $causationId,
        Id $correlationId,
        \DateTimeImmutable $recordedOn,
        array $metadata,
        string $withIp,
        string $withExistingSessionToken,
        string $refreshedSessionToken,
    ): TokenRefreshed {
        $event = new self(
            $eventId,
            $aggregateId,
            $aggregateVersion,
            $causationId,
            $correlationId,
            $recordedOn,
            $metadata
        );
        $event->withIp = $withIp;
        $event->withExistingSessionToken = $withExistingSessionToken;
        $event->refreshedSessionToken = $refreshedSessionToken;

        return $event;
    }

    public function transformSession(Session $session): Session
    {
        return Session::fromProperties(
            $session->aggregateId(),
            $this->aggregateVersion,
            SessionDetails::fromProperties(
                $session->sessionDetails()->asUser(),
                $session->sessionDetails()->withUsername(),
                $session->sessionDetails()->withEmail(),
                $session->sessionDetails()->withHashedPassword(),
                $session->sessionDetails()->byDeviceLabel(),
                $this->withIp,
                $this->refreshedSessionToken
            ),
            $session->sessionIsSignedOut()
        );
    }
}
