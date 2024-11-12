<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\User\CommandHandler\SignUp;

use Galeas\Api\BoundedContext\Identity\User\Command\SignUp;
use Galeas\Api\BoundedContext\Identity\User\Event\SignedUp;
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

    public function __construct(
        EventStore $eventStore,
    ) {
        $this->eventStore = $eventStore;
    }

    /**
     * @return array{userId: string}
     *
     * @throws InvalidEmail|InvalidPassword|InvalidUsername|TermsAreNotAgreedTo
     * @throws CouldNotHashWithBCrypt
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
        $this->eventStore->completeTransaction();

        return [
            'userId' => $signedUp->aggregateId()->id(),
        ];
    }
}
