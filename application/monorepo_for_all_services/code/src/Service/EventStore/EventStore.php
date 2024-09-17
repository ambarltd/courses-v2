<?php

declare(strict_types=1);

namespace Galeas\Api\Service\EventStore;

use Galeas\Api\Common\Event\Event;
use Galeas\Api\Common\ExceptionBase\EventStoreCannotRead;
use Galeas\Api\Common\ExceptionBase\EventStoreCannotWrite;

interface EventStore
{
    /**
     * @throws EventStoreCannotWrite
     */
    public function beginTransaction(): void;

    /**
     * @throws EventStoreCannotWrite
     */
    public function completeTransaction(): void;

    /**
     * @throws EventStoreCannotWrite
     */
    public function cancelTransaction(): void;

    /**
     * @throws EventStoreCannotRead
     */
    public function find(string $aggregateId): ?AggregateAndEventIds;

    /**
     * @throws EventStoreCannotRead
     */
    public function findEvent(string $eventId): ?Event;

    /**
     * @throws EventStoreCannotWrite
     */
    public function save(Event $event): void;
}
