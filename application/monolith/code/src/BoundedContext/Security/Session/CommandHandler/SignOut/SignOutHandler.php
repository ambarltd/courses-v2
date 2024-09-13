<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Security\Session\CommandHandler\SignOut;

use Galeas\Api\BoundedContext\Security\Session\Aggregate\Session;
use Galeas\Api\BoundedContext\Security\Session\Command\SignOut;
use Galeas\Api\BoundedContext\Security\Session\Event\SignedOut;
use Galeas\Api\Common\ExceptionBase\EventStoreCannotRead;
use Galeas\Api\Common\ExceptionBase\EventStoreCannotWrite;
use Galeas\Api\Common\ExceptionBase\ProjectionCannotRead;
use Galeas\Api\Common\ExceptionBase\QueuingFailure;
use Galeas\Api\Common\Id\Id;
use Galeas\Api\Common\Id\InvalidId;
use Galeas\Api\Service\EventStore\EventStore;
use Galeas\Api\Service\Queue\Queue;

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
     * @throws EventStoreCannotWrite|EventStoreCannotRead|QueuingFailure|ProjectionCannotRead
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

        $session = $this->eventStore->find($sessionId);

        if (!($session instanceof Session)) {
            throw new NoSessionFound();
        }

        if ($command->authorizerId !== $session->sessionDetails()->asUser()->id()) {
            throw new SessionUserDoesNotMatch();
        }

        if ($command->withSessionToken !== $session->sessionDetails()->withSessionToken()) {
            throw new SessionTokenDoesNotMatch();
        }

        $event = SignedOut::fromProperties(
            $session->id(),
            Id::fromId($command->authorizerId),
            $command->metadata,
            $command->withIp,
            $command->withSessionToken
        );

        $this->eventStore->save($event);
        $this->eventStore->completeTransaction();
    }
}
