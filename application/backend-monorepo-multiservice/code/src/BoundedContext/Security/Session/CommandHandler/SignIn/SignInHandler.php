<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Security\Session\CommandHandler\SignIn;

use Galeas\Api\BoundedContext\Security\Session\Command\SignIn;
use Galeas\Api\BoundedContext\Security\Session\Event\SignedIn;
use Galeas\Api\Common\Id\Id;
use Galeas\Api\Common\Id\InvalidId;
use Galeas\Api\CommonException\EventStoreCannotWrite;
use Galeas\Api\CommonException\ProjectionCannotRead;
use Galeas\Api\Primitive\PrimitiveCreation\NoRandomnessAvailable;
use Galeas\Api\Primitive\PrimitiveCreation\SessionToken\SessionTokenCreator;
use Galeas\Api\Primitive\PrimitiveValidation\Ip\IpV4AndV6Validator;
use Galeas\Api\Primitive\PrimitiveValidation\Session\DeviceLabelValidator;
use Galeas\Api\Service\EventStore\EventStore;

class SignInHandler
{
    private EventStore $eventStore;

    private UserIdFromSignInUsername $userIdFromSignInUsername;

    private UserIdFromSignInEmail $userIdFromSignInEmail;

    private HashedPasswordFromUserId $hashedPasswordFromUserId;

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
     * @return array{sessionTokenCreated: string}
     *
     * @throws InvalidDeviceLabel|InvalidPassword|NoPasswordFound|UserNotFound
     * @throws EventStoreCannotWrite|InvalidId|InvalidIp|ProjectionCannotRead
     * @throws NoRandomnessAvailable
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
            new \DateTimeImmutable('now'),
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
