<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Security\Session\CommandHandler\SignOut;

use Galeas\Api\BoundedContext\Security\Session\Aggregate\Session;
use Galeas\Api\BoundedContext\Security\Session\Command\SignOut;
use Galeas\Api\BoundedContext\Security\Session\Event\SignedOut;
use Galeas\Api\Common\ExceptionBase\EventStoreCannotRead;
use Galeas\Api\Common\ExceptionBase\EventStoreCannotWrite;
use Galeas\Api\Common\ExceptionBase\ProjectionCannotRead;
use Galeas\Api\Common\Id\Id;
use Galeas\Api\Common\Id\InvalidId;
use Galeas\Api\Service\EventStore\EventStore;

class SignOutHandler
{
    /**
     * @var EventStore
     */
    private $eventStore;

    /**
     * @var SessionIdFromSessionToken
     */
    private $sessionIdFromSessionToken;

    public function __construct(
        EventStore $eventStore,
        SessionIdFromSessionToken $sessionIdFromSessionToken
    ) {
        $this->eventStore = $eventStore;
        $this->sessionIdFromSessionToken = $sessionIdFromSessionToken;
    }

    /**
     * @throws NoSessionFound|SessionUserDoesNotMatch|SessionTokenDoesNotMatch|InvalidId
     * @throws EventStoreCannotWrite|EventStoreCannotRead|ProjectionCannotRead
     */
    public function handle(SignOut $command): void
    {
        $sessionId = $this->sessionIdFromSessionToken->sessionIdFromSessionToken(
            $command->withSessionToken
        );

        if (null === $sessionId) {
            throw new NoSessionFound();
        }

        $this->eventStore->beginTransaction();

        $aggregateAndEventIds = $this->eventStore->find($sessionId);

        if (null === $aggregateAndEventIds) {
            throw new NoSessionFound();
        }

        $session = $aggregateAndEventIds->aggregate();
        if (!($session instanceof Session)) {
            throw new NoSessionFound();
        }

        if ($command->authenticatedUserId !== $session->sessionDetails()->asUser()->id()) {
            throw new SessionUserDoesNotMatch();
        }

        if ($command->withSessionToken !== $session->sessionDetails()->withSessionToken()) {
            throw new SessionTokenDoesNotMatch();
        }

        $event = SignedOut::new(
            Id::createNew(),
            $session->aggregateId(),
            $session->aggregateVersion() + 1,
            $aggregateAndEventIds->lastEventId(),
            $aggregateAndEventIds->firstEventId(),
            new \DateTimeImmutable("now"),
            $command->metadata,
            $command->withIp,
            $command->withSessionToken
        );

        $this->eventStore->save($event);
        $this->eventStore->completeTransaction();
    }
}
