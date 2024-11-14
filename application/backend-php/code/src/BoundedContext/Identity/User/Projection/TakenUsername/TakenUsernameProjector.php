<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\User\Projection\TakenUsername;

use Doctrine\ODM\MongoDB\DocumentManager;
use Galeas\Api\BoundedContext\Identity\User\Event\PrimaryEmailVerified;
use Galeas\Api\BoundedContext\Identity\User\Event\SignedUp;
use Galeas\Api\Common\Event\Event;
use Galeas\Api\Service\QueueProcessor\EventProjector;

class TakenUsernameProjector extends EventProjector
{
    public function __construct(DocumentManager $projectionDocumentManager)
    {
        $this->projectionDocumentManager = $projectionDocumentManager;
    }

    protected function project(Event $event): void
    {
        if ($event instanceof SignedUp) {
            $this->saveOne(
                TakenUsername::fromUserIdAndUsername(
                    $event->aggregateId()->id(),
                    $event->username(),
                    false
                )
            );
        }
        if ($event instanceof PrimaryEmailVerified) {
            $takenUsername = $this->getOne(TakenUsername::class, ['id' => $event->aggregateId()->id()]);
            $this->saveOne($takenUsername?->verify());
        }
    }
}
