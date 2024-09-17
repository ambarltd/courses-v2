<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\User\Projection\TakenEmail;

use Doctrine\ODM\MongoDB\DocumentManager;
use Galeas\Api\BoundedContext\Identity\User\Event\PrimaryEmailChangeRequested;
use Galeas\Api\BoundedContext\Identity\User\Event\PrimaryEmailVerified;
use Galeas\Api\BoundedContext\Identity\User\Event\SignedUp;
use Galeas\Api\Common\Event\Event;
use Galeas\Api\Common\ExceptionBase\ProjectionCannotProcess;
use Galeas\Api\Service\QueueProcessor\EventProjector;

class TakenEmailProjector implements EventProjector
{
    /**
     * @var DocumentManager
     */
    private $projectionDocumentManager;

    public function __construct(DocumentManager $projectionDocumentManager)
    {
        $this->projectionDocumentManager = $projectionDocumentManager;
    }

    public function project(Event $event): void
    {
        try {
            if (
                false === ($event instanceof SignedUp) &&
                false === ($event instanceof PrimaryEmailVerified) &&
                false === ($event instanceof PrimaryEmailChangeRequested)
            ) {
                return;
            }

            $takenEmail = $this->projectionDocumentManager
                ->createQueryBuilder(TakenEmail::class)
                ->field('id')->equals($event->aggregateId()->id())
                ->getQuery()
                ->getSingleResult();

            if (
                null === $takenEmail &&
                $event instanceof SignedUp
            ) {
                // email used to sign up is taken
                $takenEmail = TakenEmail::fromUserIdAndEmails(
                    $event->aggregateId()->id(),
                    null,
                    $event->primaryEmail()
                );
            } elseif (
                $takenEmail instanceof TakenEmail &&
                $event instanceof PrimaryEmailVerified
            ) {
                // requested email becomes verified email
                $takenEmail->changeEmails(
                    $takenEmail->getCanonicalRequestedEmail(),
                    null
                );
            } elseif (
                $takenEmail instanceof TakenEmail &&
                $event instanceof PrimaryEmailChangeRequested
            ) {
                // verified email stays the same, requested email changes
                $takenEmail->changeEmails(
                    $takenEmail->getCanonicalVerifiedEmail(),
                    $event->newEmailRequested()
                );
            } else {
                throw new \Exception(sprintf('Could not process serialized event %s of class %s where TakenEmail for userId %s was found', $event->eventId()->id(), get_class($event), $event->authenticatedUserId()->id()));
            }

            $this->projectionDocumentManager->persist($takenEmail);
            $this->projectionDocumentManager->flush();
        } catch (\Throwable $exception) {
            throw new ProjectionCannotProcess($exception);
        }
    }
}
