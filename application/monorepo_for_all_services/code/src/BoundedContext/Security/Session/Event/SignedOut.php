<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Security\Session\Event;

use Galeas\Api\BoundedContext\Security\Session\Aggregate\Session;
use Galeas\Api\BoundedContext\Security\Session\ValueObject\SessionIsSignedOut;
use Galeas\Api\Common\Event\EventTrait;
use Galeas\Api\Common\Id\Id;

class SignedOut implements EventTransformedSession
{
    use EventTrait;

    private string $withIp;

    private string $withSessionToken;

    public function withIp(): string
    {
        return $this->withIp;
    }

    public function withSessionToken(): string
    {
        return $this->withSessionToken;
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
        string $withSessionToken
    ): self {
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
        $event->withSessionToken = $withSessionToken;

        return $event;
    }

    public function transformSession(Session $session): Session
    {
        return Session::fromProperties(
            $session->aggregateId(),
            $this->aggregateVersion,
            $session->sessionDetails(),
            SessionIsSignedOut::fromProperties(
                $this->withSessionToken,
                $this->withIp
            )
        );
    }
}
