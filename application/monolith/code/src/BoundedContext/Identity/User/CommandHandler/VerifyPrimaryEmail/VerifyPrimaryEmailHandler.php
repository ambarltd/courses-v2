<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\User\CommandHandler\VerifyPrimaryEmail;

use Galeas\Api\BoundedContext\Identity\User\Aggregate\User;
use Galeas\Api\BoundedContext\Identity\User\Command\VerifyPrimaryEmail;
use Galeas\Api\BoundedContext\Identity\User\CommandHandler\RequestPrimaryEmailChange\RequestPrimaryEmailChangeHandler;
use Galeas\Api\BoundedContext\Identity\User\CommandHandler\SignUp\SignUpHandler;
use Galeas\Api\BoundedContext\Identity\User\Event\PrimaryEmailVerified;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\VerifiedButRequestedNewEmail;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\UnverifiedEmail;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\VerifiedEmail;
use Galeas\Api\Common\ExceptionBase\EventStoreCannotRead;
use Galeas\Api\Common\ExceptionBase\EventStoreCannotWrite;
use Galeas\Api\Common\ExceptionBase\ProjectionCannotRead;
use Galeas\Api\Common\Id\Id;
use Galeas\Api\Common\Id\InvalidId;
use Galeas\Api\Service\EventStore\EventStore;

class VerifyPrimaryEmailHandler
{
    private EventStore $eventStore;

    private UserIdFromPrimaryEmailVerificationCode $userIdFromVerificationCode;

    public function __construct(
        EventStore $eventStore,
        UserIdFromPrimaryEmailVerificationCode $userIdFromVerificationCode
    ) {
        $this->eventStore = $eventStore;
        $this->userIdFromVerificationCode = $userIdFromVerificationCode;
    }

    /**
     * There is no need to check if the existing requested email is taken, as there must have been a check on it previously.
     *
     * @see SignUpHandler
     * @see RequestPrimaryEmailChangeHandler
     *
     * @throws NoUserFoundForCode|EmailIsAlreadyVerified|VerificationCodeDoesNotMatch|InvalidId
     * @throws ProjectionCannotRead|EventStoreCannotRead|EventStoreCannotWrite
     */
    public function handle(VerifyPrimaryEmail $command): void
    {
        $userId = $this->userIdFromVerificationCode->userIdFromPrimaryEmailVerificationCode($command->verificationCode);

        if (null === $userId) {
            throw new NoUserFoundForCode();
        }

        $this->eventStore->beginTransaction();

        $aggregateAndEventIds = $this->eventStore->find($userId);
        if (null === $aggregateAndEventIds) {
            throw new NoUserFoundForCode();
        }

        $user = $aggregateAndEventIds->aggregate();
        if (!($user instanceof User)) {
            throw new NoUserFoundForCode();
        }

        if ($user->primaryEmailStatus() instanceof VerifiedEmail) {
            throw new EmailIsAlreadyVerified();
        }

        if (
            $user->primaryEmailStatus() instanceof UnverifiedEmail &&
            $command->verificationCode !== $user->primaryEmailStatus()->verificationCode()->verificationCode()
        ) {
            throw new VerificationCodeDoesNotMatch();
        }

        if (
            $user->primaryEmailStatus() instanceof VerifiedButRequestedNewEmail &&
            $command->verificationCode !== $user->primaryEmailStatus()->verificationCode()->verificationCode()
        ) {
            throw new VerificationCodeDoesNotMatch();
        }

        $event = PrimaryEmailVerified::new(
            Id::createNew(),
            $user->aggregateId(),
            $user->aggregateVersion() + 1,
            $aggregateAndEventIds->lastEventId(),
            $aggregateAndEventIds->firstEventId(),
            new \DateTimeImmutable("now"),
            $command->metadata,
            $command->verificationCode
        );

        $this->eventStore->save($event);
        $this->eventStore->completeTransaction();
    }
}
