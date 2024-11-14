<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\AuthenticationForAllContexts\Projection\Session;

use Doctrine\ODM\MongoDB\DocumentManager;
use Galeas\Api\BoundedContext\Security\Session\Event\SignedIn;
use Galeas\Api\BoundedContext\Security\Session\Event\SignedOut;
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
                    $event->asUser()->id(),
                    $event->sessionTokenCreated(),
                    false,
                    $event->recordedOn()
                ));

                break;

            case $event instanceof TokenRefreshed:
                $session = $this->getOne(Session::class, ['id' => $event->aggregateId()->id()]);
                $this->saveOne($session?->changeProperties(
                    $event->refreshedSessionToken(),
                    false,
                    $event->recordedOn()
                ));

                break;

            case $event instanceof SignedOut:
                $session = $this->getOne(Session::class, ['id' => $event->aggregateId()->id()]);
                if ($session instanceof Session) {
                    $this->saveOne($session->changeProperties(
                        $session->getSessionToken(),
                        true,
                        $event->recordedOn()
                    ));
                }

                break;
        }
    }
}
