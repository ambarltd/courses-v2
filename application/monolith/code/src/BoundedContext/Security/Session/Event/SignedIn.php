<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Security\Session\Event;

use Galeas\Api\BoundedContext\Security\Session\Aggregate\Session;
use Galeas\Api\BoundedContext\Security\Session\ValueObject\SessionDetails;
use Galeas\Api\Common\Event\EventTrait;
use Galeas\Api\Common\Id\Id;

class SignedIn implements EventCreatedSession
{
    use EventTrait;

    private Id $asUser;

    private string|null $withUsername;

    private string|null $withEmail;

    private string $withHashedPassword;

    private string $byDeviceLabel;

    private string $withIp;

    private string $sessionTokenCreated;

    public function asUser(): Id
    {
        return $this->asUser;
    }

    public function withUsername(): ?string
    {
        return $this->withUsername;
    }

    public function withEmail(): ?string
    {
        return $this->withEmail;
    }

    public function withHashedPassword(): string
    {
        return $this->withHashedPassword;
    }

    public function byDeviceLabel(): string
    {
        return $this->byDeviceLabel;
    }

    public function withIp(): string
    {
        return $this->withIp;
    }

    public function sessionTokenCreated(): string
    {
        return $this->sessionTokenCreated;
    }

    public static function new(
        Id $eventId,
        Id $aggregateId,
        int $aggregateVersion,
        Id $causationId,
        Id $correlationId,
        \DateTimeImmutable $recordedOn,
        array $metadata,
        Id $asUser,
        ?string $withUsername,
        ?string $withEmail,
        string $withHashedPassword,
        string $byDeviceLabel,
        string $withIp,
        string $sessionTokenCreated
    ): SignedIn {
        $event = new self(
            $eventId,
            $aggregateId,
            $aggregateVersion,
            $causationId,
            $correlationId,
            $recordedOn,
            $metadata
        );

        $event->asUser = $asUser;
        $event->withUsername = $withUsername;
        $event->withEmail = $withEmail;
        $event->withHashedPassword = $withHashedPassword;
        $event->byDeviceLabel = $byDeviceLabel;
        $event->withIp = $withIp;
        $event->sessionTokenCreated = $sessionTokenCreated;

        return $event;
    }

    public function createSession(): Session
    {
        return Session::fromProperties(
            $this->aggregateId(),
            1,
            SessionDetails::fromProperties(
                $this->asUser,
                $this->withUsername,
                $this->withEmail,
                $this->withHashedPassword,
                $this->byDeviceLabel,
                $this->withIp,
                $this->sessionTokenCreated
            ),
            null
        );
    }
}
