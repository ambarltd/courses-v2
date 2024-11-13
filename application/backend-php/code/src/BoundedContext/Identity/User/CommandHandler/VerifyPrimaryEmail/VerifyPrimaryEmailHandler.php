<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\User\CommandHandler\VerifyPrimaryEmail;

use Galeas\Api\BoundedContext\Identity\TakenEmail\Aggregate\TakenEmail;
use Galeas\Api\BoundedContext\Identity\TakenEmail\Event\AbandonedEmailRetaken;
use Galeas\Api\BoundedContext\Identity\TakenEmail\Event\EmailAbandoned;
use Galeas\Api\BoundedContext\Identity\TakenEmail\Event\EmailTaken;
use Galeas\Api\BoundedContext\Identity\User\Aggregate\User;
use Galeas\Api\BoundedContext\Identity\User\Command\VerifyPrimaryEmail;
use Galeas\Api\BoundedContext\Identity\User\Event\PrimaryEmailVerified;
use Galeas\Api\BoundedContext\Identity\User\Projection\PrimaryEmailVerificationCode\UserIdFromPrimaryEmailVerificationCode;
use Galeas\Api\BoundedContext\Identity\User\Projection\TakenUsername\IsUsernameTaken;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\UnverifiedEmail;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\VerifiedButRequestedNewEmail;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\VerifiedEmail;
use Galeas\Api\Common\Id\Id;
use Galeas\Api\CommonException\EventStoreCannotRead;
use Galeas\Api\CommonException\EventStoreCannotWrite;
use Galeas\Api\CommonException\ProjectionCannotRead;
use Galeas\Api\Primitive\PrimitiveCreation\NoRandomnessAvailable;
use Galeas\Api\Service\EventStore\EventStore;

class VerifyPrimaryEmailHandler
{
    private EventStore $eventStore;

    private UserIdFromPrimaryEmailVerificationCode $userIdFromVerificationCode;

    private IsUsernameTaken $isUsernameTaken;

    public function __construct(
        EventStore $eventStore,
        UserIdFromPrimaryEmailVerificationCode $userIdFromVerificationCode,
        IsUsernameTaken $isUsernameTaken
    ) {
        $this->eventStore = $eventStore;
        $this->userIdFromVerificationCode = $userIdFromVerificationCode;
        $this->isUsernameTaken = $isUsernameTaken;
    }

    /**
     * @throws EmailIsAlreadyVerified|NoVerifiableUserFoundForCode|VerificationCodeDoesNotMatch
     * @throws EventStoreCannotRead|EventStoreCannotWrite|NoRandomnessAvailable|ProjectionCannotRead
     * @throws AggregateIdForTakenEmailUnavailable|EmailIsTaken|UsernameIsTaken
     */
    public function handle(VerifyPrimaryEmail $command): void
    {
        $userId = $this->userIdFromVerificationCode->userIdFromPrimaryEmailVerificationCode($command->verificationCode);

        if (null === $userId) {
            throw new NoVerifiableUserFoundForCode();
        }

        $this->eventStore->beginTransaction();

        $result = $this->eventStore->findAggregateAndEventIdsInLastEvent($userId);
        if (null === $result) {
            throw new NoVerifiableUserFoundForCode();
        }

        [$user, $lastEventId, $lastEventCorrelationId] = [$result->aggregate(), $result->eventIdInLastEvent(), $result->correlationIdInLastEvent()];
        if (!$user instanceof User) {
            throw new NoVerifiableUserFoundForCode();
        }

        if ($user->primaryEmailStatus() instanceof VerifiedEmail) {
            throw new EmailIsAlreadyVerified();
        }

        if (
            $user->primaryEmailStatus() instanceof UnverifiedEmail
            && $command->verificationCode !== $user->primaryEmailStatus()->verificationCode()->verificationCode()
        ) {
            throw new VerificationCodeDoesNotMatch();
        }

        if (
            $user->primaryEmailStatus() instanceof VerifiedButRequestedNewEmail
            && $command->verificationCode !== $user->primaryEmailStatus()->verificationCode()->verificationCode()
        ) {
            throw new VerificationCodeDoesNotMatch();
        }

        if ($this->isUsernameTaken->isUsernameTaken($user->accountDetails()->username())) {
            throw new UsernameIsTaken();
        }

        $event = PrimaryEmailVerified::new(
            Id::createNew(),
            $user->aggregateId(),
            $user->aggregateVersion() + 1,
            $lastEventId,
            $lastEventCorrelationId,
            new \DateTimeImmutable('now'),
            $command->metadata,
            $command->verificationCode
        );

        $this->eventStore->save($event);

        $primaryEmailStatus = $user->primaryEmailStatus();
        if ($primaryEmailStatus instanceof UnverifiedEmail) {
            $this->takeEmail($primaryEmailStatus->email()->email(), $command->metadata, $user->aggregateId());
        }
        if ($primaryEmailStatus instanceof VerifiedButRequestedNewEmail) {
            $this->takeEmail($primaryEmailStatus->requestedEmail()->email(), $command->metadata, $user->aggregateId());
            $this->abandonTakenEmail($primaryEmailStatus->verifiedEmail()->email(), $command->metadata);
        }

        $this->eventStore->completeTransaction();
    }

    /**
     * @param array<string, mixed> $metadata
     *
     * @throws EventStoreCannotWrite|NoRandomnessAvailable
     * @throws AggregateIdForTakenEmailUnavailable|EventStoreCannotRead
     */
    public function abandonTakenEmail(string $emailToAbandon, array $metadata): void
    {
        $takenEmailAggregateId = Id::createNewByHashing(
            'Identity_TakenEmail:'.strtolower($emailToAbandon)
        );
        $takenEmailResult = $this->eventStore->findAggregateAndEventIdsInLastEvent($takenEmailAggregateId->id());

        if (null === $takenEmailResult) {
            return;
        }

        [$takenEmail, $takenEmailLastEventId, $takenEmailLastEventCorrelationId] = [
            $takenEmailResult->aggregate(),
            $takenEmailResult->eventIdInLastEvent(),
            $takenEmailResult->correlationIdInLastEvent(),
        ];

        if (!$takenEmail instanceof TakenEmail) {
            throw new AggregateIdForTakenEmailUnavailable();
        }

        if (null === $takenEmail->takenByUser()) {
            return;
        }

        $this->eventStore->save(EmailAbandoned::new(
            Id::createNew(),
            $takenEmailAggregateId,
            $takenEmail->aggregateVersion() + 1,
            $takenEmailLastEventId,
            $takenEmailLastEventCorrelationId,
            new \DateTimeImmutable('now'),
            $metadata
        ));
    }

    /**
     * @param array<string, mixed> $metadata
     *
     * @throws AggregateIdForTakenEmailUnavailable|EmailIsTaken
     * @throws EventStoreCannotRead|EventStoreCannotWrite|NoRandomnessAvailable
     */
    private function takeEmail(string $emailToTake, array $metadata, Id $takenByUser): void
    {
        $takenEmailAggregateId = Id::createNewByHashing(
            'Identity_TakenEmail:'.strtolower($emailToTake)
        );
        $takenEmailResult = $this->eventStore->findAggregateAndEventIdsInLastEvent($takenEmailAggregateId->id());

        if (null === $takenEmailResult) {
            $emailTakenEventId = Id::createNew();

            $this->eventStore->save(EmailTaken::new(
                $emailTakenEventId,
                $takenEmailAggregateId,
                1,
                $emailTakenEventId,
                $emailTakenEventId,
                new \DateTimeImmutable('now'),
                $metadata,
                strtolower($emailToTake),
                $takenByUser
            ));

            return;
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

        $this->eventStore->save(AbandonedEmailRetaken::new(
            Id::createNew(),
            $takenEmailAggregateId,
            $takenEmail->aggregateVersion() + 1,
            $takenEmailLastEventId,
            $takenEmailLastEventCorrelationId,
            new \DateTimeImmutable('now'),
            $metadata,
            $takenByUser
        ));
    }
}
