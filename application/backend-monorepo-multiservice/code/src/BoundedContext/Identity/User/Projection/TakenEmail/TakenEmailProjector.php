<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\User\Projection\TakenEmail;

use Doctrine\ODM\MongoDB\DocumentManager;
use Galeas\Api\BoundedContext\Identity\User\Event\PrimaryEmailChangeRequested;
use Galeas\Api\BoundedContext\Identity\User\Event\PrimaryEmailVerified;
use Galeas\Api\BoundedContext\Identity\User\Event\SignedUp;
use Galeas\Api\Common\Event\Event;
use Galeas\Api\CommonException\ProjectionCannotProcess;
use Galeas\Api\Service\QueueProcessor\EventProjector;

class TakenEmailProjector implements EventProjector
{
    private DocumentManager $projectionDocumentManager;

    public function __construct(DocumentManager $projectionDocumentManager)
    {
        $this->projectionDocumentManager = $projectionDocumentManager;
    }

    public function project(Event $event): void
    {
        try {
            if (
                false === ($event instanceof SignedUp)
                && false === ($event instanceof PrimaryEmailVerified)
                && false === ($event instanceof PrimaryEmailChangeRequested)
            ) {
                return;
            }

            /** @var null|TakenEmail $takenEmail */
            $takenEmail = $this->projectionDocumentManager
                ->createQueryBuilder(TakenEmail::class)
                ->field('id')->equals($event->aggregateId()->id())
                ->getQuery()
                ->getSingleResult()
            ;

            if ($event instanceof SignedUp) {
                // email used to sign up is taken
                $takenEmail = TakenEmail::fromUserIdAndEmails(
                    $event->aggregateId()->id(),
                    null,
                    $event->primaryEmail()
                );
            } elseif ($event instanceof PrimaryEmailVerified && $takenEmail instanceof TakenEmail) {
                // requested email becomes verified email
                $takenEmail->changeEmails(
                    $takenEmail->getCanonicalRequestedEmail(),
                    null
                );
            } elseif ($event instanceof PrimaryEmailChangeRequested && $takenEmail instanceof TakenEmail) {
                // verified email stays the same, requested email changes
                $takenEmail->changeEmails(
                    $takenEmail->getCanonicalVerifiedEmail(),
                    $event->newEmailRequested()
                );
            } else {
                throw new \Exception(\sprintf('Could not process serialized event %s of class %s where TakenEmail for aggregateId %s was found', $event->eventId()->id(), $event::class, $event->aggregateId()->id()));
            }

            $this->projectionDocumentManager->persist($takenEmail);
            $this->projectionDocumentManager->flush();
        } catch (\Throwable $exception) {
            throw new ProjectionCannotProcess($exception);
        }
    }
}
