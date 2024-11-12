<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\User\CommandHandler\RequestPrimaryEmailChange;

use Galeas\Api\BoundedContext\Identity\User\Aggregate\User;
use Galeas\Api\BoundedContext\Identity\User\Command\RequestPrimaryEmailChange;
use Galeas\Api\BoundedContext\Identity\User\CommandHandler\VerifyPrimaryEmail\AggregateIdForTakenEmailUnavailable;
use Galeas\Api\BoundedContext\Identity\User\CommandHandler\VerifyPrimaryEmail\NoVerifiableUserFoundForCode;
use Galeas\Api\BoundedContext\Identity\User\Event\PrimaryEmailChangeRequested;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\UnverifiedEmail;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\VerifiedButRequestedNewEmail;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\VerifiedEmail;
use Galeas\Api\Common\Id\Id;
use Galeas\Api\CommonException\EventStoreCannotRead;
use Galeas\Api\CommonException\EventStoreCannotWrite;
use Galeas\Api\Primitive\PrimitiveCreation\Email\EmailVerificationCodeCreator;
use Galeas\Api\Primitive\PrimitiveCreation\NoRandomnessAvailable;
use Galeas\Api\Primitive\PrimitiveValidation\Email\EmailValidator;
use Galeas\Api\Service\EventStore\EventStore;

class RequestPrimaryEmailChangeHandler
{
    private EventStore $eventStore;

    public function __construct(
        EventStore $eventStore,
    ) {
        $this->eventStore = $eventStore;
    }

    /**
     * @throws EmailIsNotChanging|PasswordDoesNotMatch|UserNotFound
     * @throws InvalidEmail|NoVerifiableUserFoundForCode
     * @throws EventStoreCannotRead|EventStoreCannotWrite
     * @throws AggregateIdForTakenEmailUnavailable|NoRandomnessAvailable
     * @throws AggregateIdForTakenEmailUnavailable
     */
    public function handle(RequestPrimaryEmailChange $command): void
    {
        $this->eventStore->beginTransaction();

        $result = $this->eventStore->findAggregateAndEventIdsInLastEvent($command->authenticatedUserId);
        if (null === $result) {
            throw new UserNotFound();
        }

        [$user, $lastEventId, $lastEventCorrelationId] = [$result->aggregate(), $result->eventIdInLastEvent(), $result->correlationIdInLastEvent()];
        if (!$user instanceof User) {
            throw new NoVerifiableUserFoundForCode();
        }

        if ($user->primaryEmailStatus() instanceof UnverifiedEmail) {
            throw new UnverifiedUserCannotRequestPrimaryEmailChange();
        }

        $emailIsNotChanging =
            (
                $user->primaryEmailStatus() instanceof VerifiedEmail
                && strtolower($user->primaryEmailStatus()->email()->email()) === strtolower($command->newEmailRequested)
            )
            || (
                $user->primaryEmailStatus() instanceof VerifiedButRequestedNewEmail
                && strtolower($user->primaryEmailStatus()->verifiedEmail()->email()) === strtolower($command->newEmailRequested)
            )
            || (
                $user->primaryEmailStatus() instanceof VerifiedButRequestedNewEmail
                && strtolower($user->primaryEmailStatus()->requestedEmail()->email()) === strtolower($command->newEmailRequested)
            );

        if ($emailIsNotChanging) {
            throw new EmailIsNotChanging();
        }

        if (false === password_verify($command->password, $user->hashedPassword()->hash())) {
            throw new PasswordDoesNotMatch();
        }

        if (false === EmailValidator::isValid($command->newEmailRequested)) {
            throw new InvalidEmail();
        }

        $primaryEmailChangeRequested = PrimaryEmailChangeRequested::new(
            Id::createNew(),
            $user->aggregateId(),
            $user->aggregateVersion() + 1,
            $lastEventId,
            $lastEventCorrelationId,
            new \DateTimeImmutable('now'),
            $command->metadata,
            $command->newEmailRequested,
            EmailVerificationCodeCreator::create(),
            $user->hashedPassword()->hash()
        );

        $this->eventStore->save($primaryEmailChangeRequested);
        $this->eventStore->completeTransaction();
    }
}
