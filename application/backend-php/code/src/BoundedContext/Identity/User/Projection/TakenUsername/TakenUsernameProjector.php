<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\User\Projection\TakenUsername;

use Doctrine\ODM\MongoDB\DocumentManager;
use Galeas\Api\BoundedContext\Identity\User\Event\SignedUp;
use Galeas\Api\Common\Event\Event;
use Galeas\Api\CommonException\ProjectionCannotProcess;
use Galeas\Api\Service\QueueProcessor\EventProjector;

class TakenUsernameProjector extends EventProjector
{
    public function __construct(DocumentManager $projectionDocumentManager)
    {
        $this->projectionDocumentManager = $projectionDocumentManager;
    }

    public function project(Event $event): void
    {
        try {
            if ($event instanceof SignedUp) {
                $this->saveOne(
                    TakenUsername::fromUserIdAndUsername(
                        $event->aggregateId()->id(),
                        $event->username()
                    )
                );
            }
            $this->commitProjection($event, 'Identity_User_TakenUsername');
        } catch (\Throwable $throwable) {
            throw new ProjectionCannotProcess($throwable);
        }
    }
}
