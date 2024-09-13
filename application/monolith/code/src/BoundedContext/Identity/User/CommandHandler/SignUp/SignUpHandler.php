<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\User\CommandHandler\SignUp;

use Galeas\Api\BoundedContext\Identity\User\Command\SignUp;
use Galeas\Api\BoundedContext\Identity\User\Event\CouldNotHashWithBCrypt;
use Galeas\Api\BoundedContext\Identity\User\Event\SignedUp;
use Galeas\Api\Common\ExceptionBase\EventStoreCannotWrite;
use Galeas\Api\Common\ExceptionBase\ProjectionCannotRead;
use Galeas\Api\Common\ExceptionBase\QueuingFailure;
use Galeas\Api\Primitive\PrimitiveValidation\Email\EmailValidator;
use Galeas\Api\Primitive\PrimitiveValidation\Security\PasswordValidator;
use Galeas\Api\Primitive\PrimitiveValidation\Username\UsernameValidator;
use Galeas\Api\Service\EventStore\EventStore;
use Galeas\Api\Service\Queue\Queue;

class SignUpHandler
{
    /**
     * @var EventStore
     */
    private $eventStore;

    /**
     * @var IsEmailTaken
     */
    private $isEmailTaken;

    /**
     * @var IsUsernameTaken
     */
    private $isUsernameTaken;

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
     * @throws EventStoreCannotWrite|QueuingFailure|ProjectionCannotRead
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

        if (false === $command->termsOfUseAccepted) {
            throw new TermsAreNotAgreedTo();
        }

        if (true === $this->isEmailTaken->isEmailTaken($command->primaryEmail)) {
            throw new EmailIsTaken();
        }

        if (true === $this->isUsernameTaken->isUsernameTaken($command->username)) {
            throw new UsernameIsTaken();
        }

        $event = SignedUp::fromProperties(
            $command->metadata,
            $command->primaryEmail,
            $command->password,
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
