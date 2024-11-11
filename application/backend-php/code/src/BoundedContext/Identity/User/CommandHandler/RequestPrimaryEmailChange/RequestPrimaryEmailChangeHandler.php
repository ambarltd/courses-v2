<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\User\CommandHandler\RequestPrimaryEmailChange;

use Galeas\Api\BoundedContext\Identity\TakenEmail\Aggregate\TakenEmail;
use Galeas\Api\BoundedContext\Identity\TakenEmail\Event\AbandonedEmailRetaken;
use Galeas\Api\BoundedContext\Identity\TakenEmail\Event\EmailTaken;
use Galeas\Api\BoundedContext\Identity\User\Aggregate\User;
use Galeas\Api\BoundedContext\Identity\User\Command\RequestPrimaryEmailChange;
use Galeas\Api\BoundedContext\Identity\User\CommandHandler\SignUp\SignUpHandler;
use Galeas\Api\BoundedContext\Identity\User\CommandHandler\VerifyPrimaryEmail\NoUserFoundForCode;
use Galeas\Api\BoundedContext\Identity\User\Event\PrimaryEmailChangeRequested;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\UnverifiedEmail;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\VerifiedButRequestedNewEmail;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\VerifiedEmail;
use Galeas\Api\Common\Id\Id;
use Galeas\Api\CommonException\EventStoreCannotRead;
use Galeas\Api\CommonException\EventStoreCannotWrite;
use Galeas\Api\Primitive\PrimitiveComparison\Email\AreEmailsEquivalent;
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
     * There is no need to check if the existing verified email is taken, as there must have been a check on it previously.
     *
     * @see SignUpHandler
     *
     * @throws EmailIsNotChanging|PasswordDoesNotMatch|UserNotFound
     * @throws EmailIsTaken|InvalidEmail|NoUserFoundForCode
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
            throw new NoUserFoundForCode();
        }

        if (
            $user->primaryEmailStatus() instanceof UnverifiedEmail
            && AreEmailsEquivalent::areEmailsEquivalent(
                $user->primaryEmailStatus()->email()->email(),
                $command->newEmailRequested
            )
        ) {
            throw new EmailIsNotChanging();
        }

        if (
            $user->primaryEmailStatus() instanceof VerifiedEmail
            && AreEmailsEquivalent::areEmailsEquivalent(
                $user->primaryEmailStatus()->email()->email(),
                $command->newEmailRequested
            )
        ) {
            throw new EmailIsNotChanging();
        }

        if (
            $user->primaryEmailStatus() instanceof VerifiedButRequestedNewEmail
            && AreEmailsEquivalent::areEmailsEquivalent(
                $user->primaryEmailStatus()->verifiedEmail()->email(),
                $command->newEmailRequested
            )
        ) {
            throw new EmailIsNotChanging();
        }

        if (
            $user->primaryEmailStatus() instanceof VerifiedButRequestedNewEmail
            && AreEmailsEquivalent::areEmailsEquivalent(
                $user->primaryEmailStatus()->requestedEmail()->email(),
                $command->newEmailRequested
            )
        ) {
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
        $this->eventStore->save($this->takeEmail($command, $user->aggregateId()));
        $this->eventStore->completeTransaction();
    }

    /**
     * @throws AggregateIdForTakenEmailUnavailable|EmailIsTaken
     * @throws EventStoreCannotRead|NoRandomnessAvailable
     */
    private function takeEmail(RequestPrimaryEmailChange $command, Id $userAggregateId): AbandonedEmailRetaken|EmailTaken
    {
        $takenEmailAggregateId = Id::createNewByHashing(
            'Identity_TakenEmail:'.strtolower($command->newEmailRequested)
        );
        $takenEmailResult = $this->eventStore->findAggregateAndEventIdsInLastEvent($takenEmailAggregateId->id());

        if (null === $takenEmailResult) {
            $emailTakenEventId = Id::createNew();

            return EmailTaken::new(
                $emailTakenEventId,
                $takenEmailAggregateId,
                1,
                $emailTakenEventId,
                $emailTakenEventId,
                new \DateTimeImmutable('now'),
                $command->metadata,
                $command->newEmailRequested,
                $userAggregateId
            );
        }
        [$takenEmail, $takenEmailLastEventId, $takenEmailLastEventCorrelationId] = [
            $takenEmailResult->aggregate(),
            $takenEmailResult->eventIdInLastEvent(),
            $takenEmailResult->correlationIdInLastEvent(),
        ];

        if (!$takenEmail instanceof TakenEmail) {
            throw new AggregateIdForTakenEmailUnavailable();
        }

        if (null !== $takenEmail->takenByUser()) {
            throw new EmailIsTaken();
        }

        return AbandonedEmailRetaken::new(
            Id::createNew(),
            $takenEmailAggregateId,
            $takenEmail->aggregateVersion() + 1,
            $takenEmailLastEventId,
            $takenEmailLastEventCorrelationId,
            new \DateTimeImmutable('now'),
            $command->metadata,
            $userAggregateId
        );
    }
}
