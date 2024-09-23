<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Security\Session\CommandHandler\RefreshToken;

use Galeas\Api\BoundedContext\Security\Session\Aggregate\Session;
use Galeas\Api\BoundedContext\Security\Session\Command\RefreshToken;
use Galeas\Api\BoundedContext\Security\Session\Event\TokenRefreshed;
use Galeas\Api\Common\Id\Id;
use Galeas\Api\CommonException\EventStoreCannotRead;
use Galeas\Api\CommonException\EventStoreCannotWrite;
use Galeas\Api\CommonException\ProjectionCannotRead;
use Galeas\Api\Primitive\PrimitiveCreation\NoRandomnessAvailable;
use Galeas\Api\Primitive\PrimitiveCreation\SessionToken\SessionTokenCreator;
use Galeas\Api\Primitive\PrimitiveValidation\Ip\IpV4AndV6Validator;
use Galeas\Api\Service\EventStore\EventStore;

class RefreshTokenHandler
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
     * @return array{refreshedSessionToken: string}
     *
     * @throws InvalidIp|NoSessionFound|SessionTokenDoesNotMatch|SessionUserDoesNotMatch
     * @throws AlreadySignedOut|EventStoreCannotRead|EventStoreCannotWrite|ProjectionCannotRead
     * @throws NoRandomnessAvailable
     */
    public function handle(RefreshToken $command): array
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

        if (null !== $session->sessionIsSignedOut()) {
            throw new AlreadySignedOut();
        }

        if ($command->authenticatedUserId !== $session->sessionDetails()->asUser()->id()) {
            throw new SessionUserDoesNotMatch();
        }

        if ($command->withSessionToken !== $session->sessionDetails()->withSessionToken()) {
            throw new SessionTokenDoesNotMatch();
        }

        if (false === IpV4AndV6Validator::isValid($command->withIp)) {
            throw new InvalidIp();
        }

        $event = TokenRefreshed::new(
            Id::createNew(),
            $session->aggregateId(),
            $session->aggregateVersion() + 1,
            $aggregateAndEventIds->lastEventId(),
            $aggregateAndEventIds->firstEventId(),
            new \DateTimeImmutable('now'),
            $command->metadata,
            $command->withIp,
            $command->withSessionToken,
            SessionTokenCreator::create()
        );

        $this->eventStore->save($event);
        $this->eventStore->completeTransaction();

        return [
            'refreshedSessionToken' => $event->refreshedSessionToken(),
        ];
    }
}
