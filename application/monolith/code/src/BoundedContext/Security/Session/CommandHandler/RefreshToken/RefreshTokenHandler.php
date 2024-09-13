<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Security\Session\CommandHandler\RefreshToken;

use Galeas\Api\BoundedContext\Security\Session\Aggregate\Session;
use Galeas\Api\BoundedContext\Security\Session\Command\RefreshToken;
use Galeas\Api\BoundedContext\Security\Session\Event\TokenRefreshed;
use Galeas\Api\Common\ExceptionBase\EventStoreCannotRead;
use Galeas\Api\Common\ExceptionBase\EventStoreCannotWrite;
use Galeas\Api\Common\ExceptionBase\ProjectionCannotRead;
use Galeas\Api\Common\ExceptionBase\QueuingFailure;
use Galeas\Api\Common\Id\Id;
use Galeas\Api\Common\Id\InvalidId;
use Galeas\Api\Primitive\PrimitiveValidation\Ip\IpV4AndV6Validator;
use Galeas\Api\Service\EventStore\EventStore;
use Galeas\Api\Service\Queue\Queue;

class RefreshTokenHandler
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
     * @throws NoSessionFound|SessionUserDoesNotMatch|SessionTokenDoesNotMatch|InvalidIp|InvalidId
     * @throws EventStoreCannotRead|EventStoreCannotWrite|QueuingFailure|ProjectionCannotRead
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

        if (false === IpV4AndV6Validator::isValid($command->withIp)) {
            throw new InvalidIp();
        }

        $event = TokenRefreshed::fromProperties(
            $session->id(),
            Id::fromId($command->authorizerId),
            $command->metadata,
            $command->withIp,
            $command->withSessionToken
        );

        $this->eventStore->save($event);
        $this->eventStore->completeTransaction();

        return [
            'refreshedSessionToken' => $event->refreshedSessionToken(),
        ];
    }
}
