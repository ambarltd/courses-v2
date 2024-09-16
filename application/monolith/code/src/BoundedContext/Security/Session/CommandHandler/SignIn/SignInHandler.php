<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Security\Session\CommandHandler\SignIn;

use Galeas\Api\BoundedContext\Security\Session\Command\SignIn;
use Galeas\Api\BoundedContext\Security\Session\Event\SignedIn;
use Galeas\Api\Common\ExceptionBase\EventStoreCannotWrite;
use Galeas\Api\Common\ExceptionBase\ProjectionCannotRead;
use Galeas\Api\Common\Id\Id;
use Galeas\Api\Common\Id\InvalidId;
use Galeas\Api\Primitive\PrimitiveCreation\SessionToken\SessionTokenCreator;
use Galeas\Api\Primitive\PrimitiveValidation\Ip\IpV4AndV6Validator;
use Galeas\Api\Primitive\PrimitiveValidation\Session\DeviceLabelValidator;
use Galeas\Api\Service\EventStore\EventStore;

class SignInHandler
{
    /**
     * @var EventStore
     */
    private $eventStore;

    /**
     * @var UserIdFromSignInUsername
     */
    private $userIdFromSignInUsername;

    /**
     * @var UserIdFromSignInEmail
     */
    private $userIdFromSignInEmail;

    /**
     * @var HashedPasswordFromUserId
     */
    private $hashedPasswordFromUserId;

    public function __construct(
        EventStore $eventStore,
        UserIdFromSignInUsername $userIdFromSignInUsername,
        UserIdFromSignInEmail $userIdFromSignInEmail,
        HashedPasswordFromUserId $hashedPasswordFromUserId
    ) {
        $this->eventStore = $eventStore;
        $this->userIdFromSignInUsername = $userIdFromSignInUsername;
        $this->userIdFromSignInEmail = $userIdFromSignInEmail;
        $this->hashedPasswordFromUserId = $hashedPasswordFromUserId;
    }

    /**
     * @throws UserNotFound|NoPasswordFound|InvalidPassword|InvalidDeviceLabel
     * @throws InvalidIp|InvalidId|EventStoreCannotWrite|ProjectionCannotRead
     */
    public function handle(SignIn $command): array
    {
        $userId = null;
        $withUsername = null;
        $withEmail = null;

        if (null !== $this->userIdFromSignInUsername->userIdFromSignInUsername($command->withUsernameOrEmail)) {
            $userId = $this->userIdFromSignInUsername->userIdFromSignInUsername($command->withUsernameOrEmail);
            $withUsername = $command->withUsernameOrEmail;
        }

        if (null !== $this->userIdFromSignInEmail->userIdFromSignInEmail($command->withUsernameOrEmail)) {
            $userId = $this->userIdFromSignInEmail->userIdFromSignInEmail($command->withUsernameOrEmail);
            $withEmail = $command->withUsernameOrEmail;
        }

        if (null === $userId) {
            throw new UserNotFound();
        }

        $hashedPasswordFromUserId = $this->hashedPasswordFromUserId->hashedPasswordFromUserId($userId);

        if (null === $hashedPasswordFromUserId) {
            throw new NoPasswordFound();
        }

        if (false === password_verify($command->withPassword, $hashedPasswordFromUserId)) {
            throw new InvalidPassword();
        }

        if (false === DeviceLabelValidator::isValid($command->byDeviceLabel)) {
            throw new InvalidDeviceLabel();
        }

        if (false === IpV4AndV6Validator::isValid($command->withIp)) {
            throw new InvalidIp();
        }

        $eventId = Id::createNew();
        $aggregateId = Id::createNew();
        $event = SignedIn::new(
            $eventId,
            $aggregateId,
            1,
            $eventId,
            $eventId,
            new \DateTimeImmutable("now"),
            $command->metadata,
            Id::fromId($userId),
            $withUsername,
            $withEmail,
            $hashedPasswordFromUserId,
            $command->byDeviceLabel,
            $command->withIp,
            SessionTokenCreator::create()
        );

        $this->eventStore->beginTransaction();
        $this->eventStore->save($event);
        $this->eventStore->completeTransaction();

        return [
            'sessionTokenCreated' => $event->sessionTokenCreated(),
        ];
    }
}
