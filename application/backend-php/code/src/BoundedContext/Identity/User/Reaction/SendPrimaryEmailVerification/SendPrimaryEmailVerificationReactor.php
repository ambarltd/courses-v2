<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\User\Reaction\SendPrimaryEmailVerification;

use Galeas\Api\BoundedContext\Identity\User\Aggregate\User;
use Galeas\Api\BoundedContext\Identity\User\Event\PrimaryEmailChangeRequested;
use Galeas\Api\BoundedContext\Identity\User\Event\PrimaryEmailVerificationCodeSent;
use Galeas\Api\BoundedContext\Identity\User\Event\SignedUp;
use Galeas\Api\Common\Event\Event;
use Galeas\Api\Common\Id\Id;
use Galeas\Api\CommonException\EventStoreCannotRead;
use Galeas\Api\CommonException\EventStoreCannotWrite;
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
     * @throws EventStoreCannotRead|EventStoreCannotWrite
     * @throws NoUserFoundForEventAggregateId
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
            return;
        }

        $fromEmailAddress = 'example.ambar.cloud';
        $subjectLine = 'Your Verification Code';
        $emailContents = 'This is your verification code: '.$verificationCode;
        //        We're not sending emails, but there is a fake inbox
        //        where users can check their verification codes.
        //        We're doing this because not everyone has access to
        //        send emails from their local machines.
        //        $this->emailer->send(
        //            $sendToEmailAddress,
        //            $subjectLine,
        //            $emailContents,
        //            $fromEmailAddress
        //        );

        $result = $this->eventStore->findAggregateAndEventIdsInLastEvent($event->aggregateId()->id());
        if (null === $result) {
            throw new NoUserFoundForEventAggregateId();
        }

        [$user, $lastEventId, $lastEventCorrelationId] = [$result->aggregate(), $result->eventIdInLastEvent(), $result->correlationIdInLastEvent()];
        if (!$user instanceof User) {
            throw new NoUserFoundForEventAggregateId();
        }

        $newEvent = PrimaryEmailVerificationCodeSent::new(
            $newEventId,
            $user->aggregateId(),
            $user->aggregateVersion() + 1,
            $lastEventId,
            $lastEventCorrelationId,
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
