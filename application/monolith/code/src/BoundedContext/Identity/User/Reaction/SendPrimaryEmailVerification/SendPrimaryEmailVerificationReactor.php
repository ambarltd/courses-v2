<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\User\Reaction\SendPrimaryEmailVerification;

use Galeas\Api\BoundedContext\Identity\User\Event\PrimaryEmailChangeRequested;
use Galeas\Api\BoundedContext\Identity\User\Event\PrimaryEmailVerificationCodeSent;
use Galeas\Api\BoundedContext\Identity\User\Event\SignedUp;
use Galeas\Api\Common\Event\Event;
use Galeas\Api\Common\ExceptionBase\EventStoreCannotRead;
use Galeas\Api\Common\ExceptionBase\EventStoreCannotWrite;
use Galeas\Api\Common\ExceptionBase\ProjectionCannotRead;
use Galeas\Api\Common\ExceptionBase\QueuingFailure;
use Galeas\Api\Common\Id\Id;
use Galeas\Api\Service\EventStore\EventStore;

class SendPrimaryEmailVerificationReactor
{
    /**
     * @var EventStore
     */
    private $eventStore;

    /**
     * @var EmailService
     * Todo
     */
    private $emailService;

    public function __construct(EventStore $eventStore) {
        $this->eventStore = $eventStore;
    }

    /**
     * @throws PrimaryEmailVerificationAlreadySent
     * @throws ProjectionCannotRead|EventStoreCannotRead|EventStoreCannotWrite|QueuingFailure
     */
    public function react(Event $event): void
    {
        if ($event instanceof SignedUp) {
            $sendToEmailAddress = $event->primaryEmail();
            $verificationCode = $event->primaryEmailVerificationCode();
        } elseif ($event instanceof PrimaryEmailChangeRequested) {
            $sendToEmailAddress = $event->newEmailRequested();
            $verificationCode = $event->newVerificationCode();
        } else {
            return;
        }

        $this->eventStore->beginTransaction();
        $newEventId = Id::newDeterministicIdFromAnotherId(
            "Identity/User/PrimaryEmailVerificationSent:" . $event->eventId()->id()
        );
        $existingEvent = $this->eventStore->findEvent($newEventId->id());

        if ($existingEvent instanceof Event) {
            throw new PrimaryEmailVerificationAlreadySent();
        }

        $this->emailService->sendEmail(
            "email@example.com",
            "https://domain.example/frontend/verifyEmail?verificationCode=verificationCode",
            "text"
        );

        $event = PrimaryEmailVerificationCodeSent::fromProperties(
            $newEventId,
            $event->aggregateId(),
            $event->eventId(),
            [],
            $sendToEmailAddress,
            "This is your verification code: https://example.com/page/?verificationCodeode=" . $verificationCode
        );

        $this->eventStore->save($event);
        try {
            $this->eventStore->completeTransaction();
        } catch (\Exception $e) {
            // todo , duplicates -> return success
        }
    }
}
