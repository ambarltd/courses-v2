<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Security\Session\Projection\UserWithUsername;

use Doctrine\ODM\MongoDB\DocumentManager;
use Galeas\Api\BoundedContext\Identity\User\Event\PrimaryEmailVerified;
use Galeas\Api\BoundedContext\Identity\User\Event\SignedUp;
use Galeas\Api\Common\Event\Event;
use Galeas\Api\Service\QueueProcessor\EventProjector;

class UserWithUsernameProjector extends EventProjector
{
    public function __construct(DocumentManager $projectionDocumentManager)
    {
        $this->projectionDocumentManager = $projectionDocumentManager;
    }

    protected function project(Event $event): void
    {
        switch (true) {
            case $event instanceof SignedUp:
                $this->saveOne(UserWithUsername::fromProperties(
                    strtolower($event->username()),
                    $event->aggregateId()->id(),
                    false
                ));

                break;

            case $event instanceof PrimaryEmailVerified:
                $userWithUsername = $this->getOne(UserWithUsername::class, ['id' => $event->aggregateId()->id()]);
                $this->saveOne($userWithUsername?->verify());

                break;
        }
    }
}
