<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Security\Session\CommandHandler\SignOut;

use Galeas\Api\BoundedContext\Security\Session\Aggregate\Session;
use Galeas\Api\BoundedContext\Security\Session\Command\SignOut;
use Galeas\Api\BoundedContext\Security\Session\Event\SignedOut;
use Galeas\Api\Common\Id\Id;
use Galeas\Api\CommonException\EventStoreCannotRead;
use Galeas\Api\CommonException\EventStoreCannotWrite;
use Galeas\Api\CommonException\ProjectionCannotRead;
use Galeas\Api\Primitive\PrimitiveCreation\NoRandomnessAvailable;
use Galeas\Api\Service\EventStore\EventStore;

class SignOutHandler
{
    private EventStore $eventStore;

    private SessionIdFromSessionToken $sessionIdFromSessionToken;

    public function __construct(
        EventStore $eventStore,
        SessionIdFromSessionToken $sessionIdFromSessionToken
    ) {
        $this->eventStore = $eventStore;
        $this->sessionIdFromSessionToken = $sessionIdFromSessionToken;
    }

    /**
     * @throws NoSessionFound|SessionTokenDoesNotMatch|SessionUserDoesNotMatch
     * @throws EventStoreCannotRead|EventStoreCannotWrite|ProjectionCannotRead
     * @throws NoRandomnessAvailable
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
        if (!$session instanceof Session) {
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
            new \DateTimeImmutable('now'),
            $command->metadata,
            $command->withIp,
            $command->withSessionToken
        );

        $this->eventStore->save($event);
        $this->eventStore->completeTransaction();
    }
}
