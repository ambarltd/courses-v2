<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Security\Session\Projection\UserWithUsername;

use Doctrine\ODM\MongoDB\DocumentManager;
use Galeas\Api\BoundedContext\Identity\User\Event\PrimaryEmailVerified;
use Galeas\Api\BoundedContext\Identity\User\Event\SignedUp;
use Galeas\Api\Common\Event\Event;
use Galeas\Api\CommonException\ProjectionCannotProcess;
use Galeas\Api\Service\QueueProcessor\EventProjector;

class UserWithUsernameProjector extends EventProjector
{
    public function __construct(DocumentManager $projectionDocumentManager)
    {
        $this->projectionDocumentManager = $projectionDocumentManager;
    }

    public function project(Event $event): void
    {
        try {
            switch ($event) {
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
                    $this->commitProjection($event, 'Security_Session_UserWithUsername');

                    break;
            }
            $this->commitProjection($event, 'Security_Session_UserWithUsername');
        } catch (\Throwable $exception) {
            throw new ProjectionCannotProcess($exception);
        }
    }
}
