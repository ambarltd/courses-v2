<?php

declare(strict_types=1);

namespace Galeas\Api\Common\Event;

use Galeas\Api\Common\Id\Id;

interface Event
{
    public function eventId(): Id;

    public function aggregateId(): Id;

    /**
     * The @see Id of the user, who issued the corresponding command,
     * that generated this event. @see \Galeas\Api\BoundedContext\Identity\User\Aggregate\User.
     *
     * In reactions this should be null (normalization).
     *
     * PHP7 allows for a return type of Id only, but not for one of null only.
     * Hence this will not be type-hinted at the language level.
     *
     * @return Id|null
     */
    public function authorizerId();

    /**
     * In reactions, the event that caused this event to happen.
     * This is the many side, of a ManyToOne relationship.
     *
     * PHP7 allows for a return type of Id only, but not for one of null only.
     * Hence this will not be type-hinted at the language level.
     *
     * @return Id|null
     */
    public function sourceEventId();

    public function eventOccurredOn(): \DateTimeImmutable;

    public function eventMetadata(): array;
}
