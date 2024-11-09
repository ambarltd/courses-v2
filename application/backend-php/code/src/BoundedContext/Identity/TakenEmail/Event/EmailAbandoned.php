<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\TakenEmail\Event;

use Galeas\Api\BoundedContext\Identity\TakenEmail\Aggregate\TakenEmail;
use Galeas\Api\Common\Event\EventTrait;
use Galeas\Api\Common\Id\Id;

class EmailAbandoned implements EventTransformedTakenEmail
{
    use EventTrait;

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
        array $metadata
    ): self {
        return new self(
            $eventId,
            $aggregateId,
            $aggregateVersion,
            $causationId,
            $correlationId,
            $recordedOn,
            $metadata
        );
    }

    public function transformTakenEmail(TakenEmail $takenEmail): TakenEmail
    {
        return TakenEmail::fromProperties(
            $this->aggregateId(),
            $this->aggregateVersion(),
            $takenEmail->takenEmailInLowercase(),
            null
        );
    }
}
