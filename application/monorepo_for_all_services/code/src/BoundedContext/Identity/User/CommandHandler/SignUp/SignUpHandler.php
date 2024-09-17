<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\User\CommandHandler\SignUp;

use Galeas\Api\BoundedContext\Identity\User\Command\SignUp;
use Galeas\Api\BoundedContext\Identity\User\Event\SignedUp;
use Galeas\Api\Common\ExceptionBase\EventStoreCannotWrite;
use Galeas\Api\Common\ExceptionBase\ProjectionCannotRead;
use Galeas\Api\Common\Id\Id;
use Galeas\Api\Primitive\PrimitiveCreation\Email\EmailVerificationCodeCreator;
use Galeas\Api\Primitive\PrimitiveTransformation\Hash\BCryptPasswordHash;
use Galeas\Api\Primitive\PrimitiveValidation\Email\EmailValidator;
use Galeas\Api\Primitive\PrimitiveValidation\Security\PasswordValidator;
use Galeas\Api\Primitive\PrimitiveValidation\Username\UsernameValidator;
use Galeas\Api\Service\EventStore\EventStore;

class SignUpHandler
{
    private EventStore $eventStore;

    private IsEmailTaken $isEmailTaken;

    private IsUsernameTaken $isUsernameTaken;

    public function __construct(
        EventStore $eventStore,
        IsEmailTaken $isEmailTaken,
        IsUsernameTaken $isUsernameTaken
    ) {
        $this->eventStore = $eventStore;
        $this->isEmailTaken = $isEmailTaken;
        $this->isUsernameTaken = $isUsernameTaken;
    }

    /**
     * @throws InvalidEmail|InvalidPassword|InvalidUsername|TermsAreNotAgreedTo
     * @throws UsernameIsTaken|EmailIsTaken|CouldNotHashWithBCrypt
     * @throws EventStoreCannotWrite|ProjectionCannotRead
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

        if (true === $this->isEmailTaken->isEmailTaken($command->primaryEmail)) {
            throw new EmailIsTaken();
        }

        if (true === $this->isUsernameTaken->isUsernameTaken($command->username)) {
            throw new UsernameIsTaken();
        }

        $eventId = Id::createNew();
        $aggregateId = Id::createNew();
        $hashedPassword = BCryptPasswordHash::hash($command->password, 10);
        if (null === $hashedPassword) {
            throw new CouldNotHashWithBCrypt();
        }
        $event = SignedUp::new(
            $eventId,
            $aggregateId,
            1,
            $eventId,
            $eventId,
            new \DateTimeImmutable("now"),
            $command->metadata,
            $command->primaryEmail,
            EmailVerificationCodeCreator::create(),
            $hashedPassword,
            $command->username,
            $command->termsOfUseAccepted
        );

        $this->eventStore->beginTransaction();
        $this->eventStore->save($event);
        $this->eventStore->completeTransaction();

        return [
            'userId' => $event->aggregateId()->id(),
        ];
    }
}
