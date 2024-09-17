<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\User\Projection\TakenUsername;

use Doctrine\ODM\MongoDB\DocumentManager;
use Galeas\Api\BoundedContext\Identity\User\Event\SignedUp;
use Galeas\Api\Common\Event\Event;
use Galeas\Api\Common\ExceptionBase\ProjectionCannotProcess;
use Galeas\Api\Service\QueueProcessor\EventProjector;

class TakenUsernameProjector implements EventProjector
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
            if ($event instanceof SignedUp) {
                $username = $event->username();
            } else {
                return;
            }

            $takenUsername = $this->projectionDocumentManager
                ->createQueryBuilder(TakenUsername::class)
                ->field('id')->equals($event->aggregateId()->id())
                ->getQuery()
                ->getSingleResult();

            if ($takenUsername instanceof TakenUsername) {
                $takenUsername->changeUsername($username);
            } elseif (null === $takenUsername) {
                $takenUsername = TakenUsername::fromUserIdAndUsername(
                    $event->aggregateId()->id(),
                    $username
                );
            } else {
                throw new \Exception('Could not process event with id '.$event->eventId()->id());
            }

            $this->projectionDocumentManager->persist($takenUsername);
            $this->projectionDocumentManager->flush();
        } catch (\Throwable $throwable) {
            throw new ProjectionCannotProcess($throwable);
        }
    }
}
