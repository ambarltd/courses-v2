<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\User\CommandHandler\SignUp;

use Galeas\Api\BoundedContext\Identity\TakenEmail\Aggregate\TakenEmail;
use Galeas\Api\BoundedContext\Identity\TakenEmail\Event\AbandonedEmailRetaken;
use Galeas\Api\BoundedContext\Identity\TakenEmail\Event\EmailTaken;
use Galeas\Api\BoundedContext\Identity\User\Command\SignUp;
use Galeas\Api\BoundedContext\Identity\User\Event\SignedUp;
use Galeas\Api\BoundedContext\Identity\User\Projection\TakenUsername\IsUsernameTaken;
use Galeas\Api\Common\Id\Id;
use Galeas\Api\CommonException\EventStoreCannotRead;
use Galeas\Api\CommonException\EventStoreCannotWrite;
use Galeas\Api\CommonException\ProjectionCannotRead;
use Galeas\Api\Primitive\PrimitiveCreation\Email\EmailVerificationCodeCreator;
use Galeas\Api\Primitive\PrimitiveCreation\NoRandomnessAvailable;
use Galeas\Api\Primitive\PrimitiveTransformation\Hash\BCryptPasswordHash;
use Galeas\Api\Primitive\PrimitiveValidation\Email\EmailValidator;
use Galeas\Api\Primitive\PrimitiveValidation\Security\PasswordValidator;
use Galeas\Api\Primitive\PrimitiveValidation\Username\UsernameValidator;
use Galeas\Api\Service\EventStore\EventStore;

class SignUpHandler
{
    private EventStore $eventStore;

    private IsUsernameTaken $isUsernameTaken;

    public function __construct(
        EventStore $eventStore,
        IsUsernameTaken $isUsernameTaken
    ) {
        $this->eventStore = $eventStore;
        $this->isUsernameTaken = $isUsernameTaken;
    }

    /**
     * @return array{userId: string}
     *
     * @throws InvalidEmail|InvalidPassword|InvalidUsername|TermsAreNotAgreedTo
     * @throws CouldNotHashWithBCrypt|EmailIsTaken|UsernameIsTaken
     * @throws EventStoreCannotWrite|NoRandomnessAvailable|ProjectionCannotRead
     * @throws AggregateIdForTakenEmailUnavailable|NoRandomnessAvailable
     * @throws EventStoreCannotRead
     */
    public function handle(SignUp $command): array
    {
        if (false === EmailValidator::isValid($command->primaryEmail)) {
            throw new InvalidEmail();
        }

        if (false === PasswordValidator::isValid($command->password)) {
            throw new InvalidPassword();
        }

        if (false === UsernameValidator::isValid($command->username)) {
            throw new InvalidUsername();
        }

        if (!$command->termsOfUseAccepted) {
            throw new TermsAreNotAgreedTo();
        }

        // This implements unique usernames asynchronously through a projection.
        if (true === $this->isUsernameTaken->isUsernameTaken($command->username)) {
            throw new UsernameIsTaken();
        }

        $this->eventStore->beginTransaction();

        $signedUpEventId = Id::createNew();
        $userAggregateId = Id::createNew();
        $hashedPassword = BCryptPasswordHash::hash($command->password, 10);
        if (null === $hashedPassword) {
            throw new CouldNotHashWithBCrypt();
        }
        $signedUp = SignedUp::new(
            $signedUpEventId,
            $userAggregateId,
            1,
            $signedUpEventId,
            $signedUpEventId,
            new \DateTimeImmutable('now'),
            $command->metadata,
            $command->primaryEmail,
            EmailVerificationCodeCreator::create(),
            $hashedPassword,
            $command->username,
            $command->termsOfUseAccepted
        );

        $this->eventStore->save($signedUp);
        // This implements unique emails per user synchronously through the event store.
        $this->eventStore->save($this->takeEmail($command, $userAggregateId));
        $this->eventStore->completeTransaction();

        return [
            'userId' => $signedUp->aggregateId()->id(),
        ];
    }

    /**
     * @throws AggregateIdForTakenEmailUnavailable|EmailIsTaken
     * @throws EventStoreCannotRead|NoRandomnessAvailable
     */
    private function takeEmail(SignUp $command, Id $userAggregateId): AbandonedEmailRetaken|EmailTaken
    {
        $takenEmailAggregateId = Id::createNewByHashing(
            'Identity_TakenEmail:'.strtolower($command->primaryEmail)
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
                $command->primaryEmail,
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
