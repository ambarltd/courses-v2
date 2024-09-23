<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\User\Reaction\SendPrimaryEmailVerification;

use Galeas\Api\BoundedContext\Identity\User\Aggregate\User;
use Galeas\Api\BoundedContext\Identity\User\CommandHandler\VerifyPrimaryEmail\NoUserFoundForCode;
use Galeas\Api\BoundedContext\Identity\User\Event\PrimaryEmailChangeRequested;
use Galeas\Api\BoundedContext\Identity\User\Event\PrimaryEmailVerificationCodeSent;
use Galeas\Api\BoundedContext\Identity\User\Event\SignedUp;
use Galeas\Api\Common\Event\Event;
use Galeas\Api\Common\Id\Id;
use Galeas\Api\CommonException\EventStoreCannotRead;
use Galeas\Api\CommonException\EventStoreCannotWrite;
use Galeas\Api\Primitive\PrimitiveCreation\NoRandomnessAvailable;
use Galeas\Api\Service\EventStore\EventStore;
use Galeas\Api\Service\QueueProcessor\EventReactor;

class SendPrimaryEmailVerificationReactor implements EventReactor
{
    private EventStore $eventStore;

    public function __construct(EventStore $eventStore)
    {
        $this->eventStore = $eventStore;
    }

    /**
     * @throws PrimaryEmailVerificationAlreadySent
     * @throws EventStoreCannotRead|EventStoreCannotWrite
     * @throws NoRandomnessAvailable|NoUserFoundForCode
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
        $newEventId = Id::createNewByHashing(
            'Identity/User/PrimaryEmailVerificationSent:'.$event->eventId()->id()
        );
        $existingReaction = $this->eventStore->findEvent($newEventId->id());
        if ($existingReaction instanceof Event) {
            throw new PrimaryEmailVerificationAlreadySent();
        }

        $fromEmailAddress = 'system.development-application.example.com';
        $subjectLine = 'Your Verification Code';
        $emailContents = 'This is your verification code: https://example.com/page/?verificationCode='.$verificationCode;
        //        We're not sending emails for now
        //        $this->emailer->send(
        //            $sendToEmailAddress,
        //            $subjectLine,
        //            $emailContents,
        //            $fromEmailAddress
        //        );

        $aggregateAndEventIds = $this->eventStore->find($event->aggregateId()->id());
        if (null === $aggregateAndEventIds) {
            throw new NoUserFoundForCode();
        }

        $user = $aggregateAndEventIds->aggregate();
        if (!$user instanceof User) {
            throw new NoUserFoundForCode();
        }

        $newEvent = PrimaryEmailVerificationCodeSent::new(
            Id::createNew(),
            $user->aggregateId(),
            $user->aggregateVersion() + 1,
            $aggregateAndEventIds->lastEventId(),
            $aggregateAndEventIds->firstEventId(),
            new \DateTimeImmutable('now'),
            [],
            $verificationCode,
            $sendToEmailAddress,
            $emailContents,
            $fromEmailAddress,
            $subjectLine
        );

        $this->eventStore->save($newEvent);
        $this->eventStore->completeTransaction();
    }
}
