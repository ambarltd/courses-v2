<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Security\Session\Projection\UserWithEmail;

use Doctrine\ODM\MongoDB\DocumentManager;
use Galeas\Api\BoundedContext\Identity\User\Event\PrimaryEmailChangeRequested;
use Galeas\Api\BoundedContext\Identity\User\Event\PrimaryEmailVerified;
use Galeas\Api\BoundedContext\Identity\User\Event\SignedUp;
use Galeas\Api\Common\Event\Event;
use Galeas\Api\Common\ExceptionBase\ProjectionCannotProcess;
use Galeas\Api\Service\QueueProcessor\EventProjector;

class UserWithEmailProjector implements EventProjector
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

            $userWithEmail = $this->projectionDocumentManager
                ->createQueryBuilder(UserWithEmail::class)
                ->field('id')->equals($event->aggregateId()->id())
                ->getQuery()
                ->getSingleResult();

            if ($event instanceof SignedUp) {
                $userWithEmail = UserWithEmail::fromUserIdAndEmails(
                    $event->aggregateId()->id(),
                    null,
                    $event->primaryEmail(),
                    Unverified::setStatus()
                );
            } elseif (
                $event instanceof PrimaryEmailVerified &&
                $userWithEmail instanceof UserWithEmail
            ) {
                if ($userWithEmail->getStatus() instanceof Unverified) {
                    $userWithEmail->changeEmails(
                        $userWithEmail->getCanonicalRequestedEmail(),
                        null,
                        Verified::setStatus()
                    );
                }
                if ($userWithEmail->getStatus() instanceof RequestedChange) {
                    $userWithEmail->changeEmails(
                        $userWithEmail->getCanonicalRequestedEmail(),
                        null,
                        Verified::setStatus()
                    );
                }
            } elseif (
                $event instanceof PrimaryEmailChangeRequested &&
                $userWithEmail instanceof UserWithEmail
            ) {
                if ($userWithEmail->getStatus() instanceof Unverified) {
                    $userWithEmail->changeEmails(
                        null,
                        $event->newEmailRequested(),
                        Unverified::setStatus()
                    );
                } elseif ($userWithEmail->getStatus() instanceof Verified) {
                    $userWithEmail->changeEmails(
                        $userWithEmail->getCanonicalVerifiedEmail(),
                        $event->newEmailRequested(),
                        RequestedChange::setStatus()
                    );
                } else {
                    $userWithEmail->changeEmails(
                        $userWithEmail->getCanonicalVerifiedEmail(),
                        $event->newEmailRequested(),
                        RequestedChange::setStatus()
                    );
                }
            } else {
                throw new \Exception(sprintf('Could not process serialized event %s of class %s where UserWithEmail for userId %s was found', $event->eventId()->id(), get_class($event), $event->authenticatedUserId()->id()));
            }

            $this->projectionDocumentManager->persist($userWithEmail);
            $this->projectionDocumentManager->flush();
        } catch (\Throwable $exception) {
            throw new ProjectionCannotProcess($exception);
        }
    }
}
