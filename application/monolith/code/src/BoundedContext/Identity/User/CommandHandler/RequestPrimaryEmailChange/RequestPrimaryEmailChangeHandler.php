<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\User\CommandHandler\RequestPrimaryEmailChange;

use Galeas\Api\BoundedContext\Identity\User\Aggregate\User;
use Galeas\Api\BoundedContext\Identity\User\Command\RequestPrimaryEmailChange;
use Galeas\Api\BoundedContext\Identity\User\CommandHandler\SignUp\SignUpHandler;
use Galeas\Api\BoundedContext\Identity\User\CommandHandler\VerifyPrimaryEmail\NoUserFoundForCode;
use Galeas\Api\BoundedContext\Identity\User\Event\PrimaryEmailChangeRequested;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\VerifiedButRequestedNewEmail;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\UnverifiedEmail;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\VerifiedEmail;
use Galeas\Api\Common\ExceptionBase\EventStoreCannotRead;
use Galeas\Api\Common\ExceptionBase\EventStoreCannotWrite;
use Galeas\Api\Common\ExceptionBase\ProjectionCannotRead;
use Galeas\Api\Common\Id\Id;
use Galeas\Api\Common\Id\InvalidId;
use Galeas\Api\Primitive\PrimitiveComparison\Email\AreEmailsEquivalent;
use Galeas\Api\Primitive\PrimitiveCreation\Email\EmailVerificationCodeCreator;
use Galeas\Api\Primitive\PrimitiveValidation\Email\EmailValidator;
use Galeas\Api\Service\EventStore\EventStore;

class RequestPrimaryEmailChangeHandler
{
    private EventStore $eventStore;

    private IsEmailTaken $isEmailTaken;

    public function __construct(
        EventStore $eventStore,
        IsEmailTaken $isEmailTaken
    ) {
        $this->eventStore = $eventStore;
        $this->isEmailTaken = $isEmailTaken;
    }

    /**
     * There is no need to check if the existing verified email is taken, as there must have been a check on it previously.
     *
     * @see SignUpHandler
     *
     * @throws UserNotFound|EmailIsNotChanging|PasswordDoesNotMatch
     * @throws EmailIsTaken|InvalidEmail|InvalidId
     * @throws ProjectionCannotRead|EventStoreCannotRead|EventStoreCannotWrite
     */
    public function handle(RequestPrimaryEmailChange $command): void
    {
        $this->eventStore->beginTransaction();

        $aggregateAndEventIds = $this->eventStore->find($command->authenticatedUserId);
        if (null === $aggregateAndEventIds) {
            throw new UserNotFound();
        }

        $user = $aggregateAndEventIds->aggregate();
        if (!($user instanceof User)) {
            throw new NoUserFoundForCode();
        }

        if (
            $user->primaryEmailStatus() instanceof UnverifiedEmail &&
            AreEmailsEquivalent::areEmailsEquivalent(
                $user->primaryEmailStatus()->email()->email(),
                $command->newEmailRequested
            )
        ) {
            throw new EmailIsNotChanging();
        }

        if (
            $user->primaryEmailStatus() instanceof VerifiedEmail &&
            AreEmailsEquivalent::areEmailsEquivalent(
                $user->primaryEmailStatus()->email()->email(),
                $command->newEmailRequested
            )
        ) {
            throw new EmailIsNotChanging();
        }

        if (
            $user->primaryEmailStatus() instanceof VerifiedButRequestedNewEmail &&
            AreEmailsEquivalent::areEmailsEquivalent(
                $user->primaryEmailStatus()->verifiedEmail()->email(),
                $command->newEmailRequested
            )
        ) {
            throw new EmailIsNotChanging();
        }

        if (
            $user->primaryEmailStatus() instanceof VerifiedButRequestedNewEmail &&
            AreEmailsEquivalent::areEmailsEquivalent(
                $user->primaryEmailStatus()->requestedEmail()->email(),
                $command->newEmailRequested
            )
        ) {
            throw new EmailIsNotChanging();
        }

        if (false === password_verify($command->password, $user->hashedPassword()->hash())) {
            throw new PasswordDoesNotMatch();
        }

        if (true === $this->isEmailTaken->isEmailTaken($command->newEmailRequested)) {
            throw new EmailIsTaken();
        }

        if (false === EmailValidator::isValid($command->newEmailRequested)) {
            throw new InvalidEmail();
        }

        $event = PrimaryEmailChangeRequested::new(
            Id::createNew(),
            $user->aggregateId(),
            $user->aggregateVersion() + 1,
            $aggregateAndEventIds->lastEventId(),
            $aggregateAndEventIds->firstEventId(),
            new \DateTimeImmutable("now"),
            $command->metadata,
            $command->newEmailRequested,
            EmailVerificationCodeCreator::create(),
            $user->hashedPassword()->hash()
        );

        $this->eventStore->save($event);
        $this->eventStore->completeTransaction();
    }
}
