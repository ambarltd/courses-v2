<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\TakenEmail\Event;

use Galeas\Api\BoundedContext\Identity\TakenEmail\Aggregate\TakenEmail;
use Galeas\Api\Common\Event\EventTrait;
use Galeas\Api\Common\Id\Id;

class EmailTaken implements EventCreatedTakenEmail
{
    use EventTrait;

    private string $takenEmailInLowercase;

    private Id $takenByUser;

    public function takenEmailInLowercase(): string
    {
        return $this->takenEmailInLowercase;
    }

    public function takenByUser(): Id
    {
        return $this->takenByUser;
    }

    /**
     * @param array<string, mixed> $metadata
     */
    public static function new(
        Id $eventId,
        Id $aggregateId,
        int $aggregateVersion,
        Id $causationId,
        Id $correlationId,
        \DateTimeImmutable $recordedOn,
        array $metadata,
        string $takenEmailInLowercase,
        Id $takenByUser
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

        $event->takenByUser = $takenByUser;
        $event->takenEmailInLowercase = $takenEmailInLowercase;

        return $event;
    }

    public function createTakenEmail(): TakenEmail
    {
        return TakenEmail::fromProperties(
            $this->aggregateId(),
            $this->aggregateVersion(),
            $this->takenEmailInLowercase,
            $this->takenByUser
        );
    }
}
