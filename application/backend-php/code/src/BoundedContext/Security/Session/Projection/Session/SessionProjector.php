<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Security\Session\Projection\Session;

use Doctrine\ODM\MongoDB\DocumentManager;
use Galeas\Api\BoundedContext\Security\Session\Event\SignedIn;
use Galeas\Api\BoundedContext\Security\Session\Event\TokenRefreshed;
use Galeas\Api\Common\Event\Event;
use Galeas\Api\Service\QueueProcessor\EventProjector;

class SessionProjector extends EventProjector
{
    public function __construct(DocumentManager $projectionDocumentManager)
    {
        $this->projectionDocumentManager = $projectionDocumentManager;
    }

    protected function project(Event $event): void
    {
        switch (true) {
            case $event instanceof SignedIn:
                $this->saveOne(Session::fromProperties(
                    $event->aggregateId()->id(),
                    $event->sessionTokenCreated(),
                ));

                break;

            case $event instanceof TokenRefreshed:
                $session = $this->getOne(Session::class, ['id' => $event->aggregateId()->id()]);
                $this->saveOne($session?->refreshToken(
                    $event->refreshedSessionToken(),
                ));

                break;
        }
    }
}
