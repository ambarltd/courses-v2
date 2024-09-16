<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\User\Projection\PrimaryEmailVerificationCode;

use Doctrine\ODM\MongoDB\DocumentManager;
use Galeas\Api\BoundedContext\Identity\User\Event\PrimaryEmailChangeRequested;
use Galeas\Api\BoundedContext\Identity\User\Event\PrimaryEmailVerified;
use Galeas\Api\BoundedContext\Identity\User\Event\SignedUp;
use Galeas\Api\Common\Event\Event;
use Galeas\Api\Common\ExceptionBase\ProjectionCannotProcess;
use Galeas\Api\Service\QueueProcessor\EventProjector;

class PrimaryEmailVerificationCodeProjector implements EventProjector
{
    private DocumentManager $projectionDocumentManager;

    public function __construct(DocumentManager $projectionDocumentManager)
    {
        $this->projectionDocumentManager = $projectionDocumentManager;
    }

    public function project(Event $event): void
    {
        try {
            if ($event instanceof SignedUp) {
                $code = $event->primaryEmailVerificationCode();
            } elseif ($event instanceof PrimaryEmailChangeRequested) {
                $code = $event->newVerificationCode();
            } elseif ($event instanceof PrimaryEmailVerified) {
                $code = null;
            } else {
                return;
            }

            $userIdToPrimaryEmailVerificationCode = $this->projectionDocumentManager
                ->createQueryBuilder(PrimaryEmailVerificationCode::class)
                ->field('id')->equals($event->aggregateId()->id())
                ->getQuery()
                ->getSingleResult();

            if (
                null !== $userIdToPrimaryEmailVerificationCode &&
                !($userIdToPrimaryEmailVerificationCode instanceof PrimaryEmailVerificationCode)
            ) {
                throw new \Exception('Could not process event with id '.$event->eventId()->id());
            }

            if (!$userIdToPrimaryEmailVerificationCode) {
                $userIdToPrimaryEmailVerificationCode = PrimaryEmailVerificationCode::fromUserIdAndVerificationCode(
                    $event->aggregateId()->id(),
                    $code
                );
            } else {
                $userIdToPrimaryEmailVerificationCode->updateVerificationCode($code);
            }

            $this->projectionDocumentManager->persist($userIdToPrimaryEmailVerificationCode);
            $this->projectionDocumentManager->flush();
        } catch (\Throwable $exception) {
            throw new ProjectionCannotProcess($exception);
        }
    }
}
