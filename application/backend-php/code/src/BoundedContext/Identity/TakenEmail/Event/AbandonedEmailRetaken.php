<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\TakenEmail\Event;

use Galeas\Api\BoundedContext\Identity\TakenEmail\Aggregate\TakenEmail;
use Galeas\Api\Common\Event\EventTrait;
use Galeas\Api\Common\Id\Id;

class AbandonedEmailRetaken implements EventTransformedTakenEmail
{
    use EventTrait;

    private Id $retakenByUser;

    public function retakenByUser(): Id
    {
        return $this->retakenByUser;
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
        Id $retakenByUser
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
        $event->retakenByUser = $retakenByUser;

        return $event;
    }

    public function transformTakenEmail(TakenEmail $takenEmail): TakenEmail
    {
        return TakenEmail::fromProperties(
            $this->aggregateId(),
            $this->aggregateVersion(),
            $takenEmail->takenEmailInLowercase(),
            $this->retakenByUser
        );
    }
}
