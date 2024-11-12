<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\User\Projection\UserDetails;

use Doctrine\ODM\MongoDB\DocumentManager;
use Galeas\Api\BoundedContext\Identity\User\Event\PrimaryEmailChangeRequested;
use Galeas\Api\BoundedContext\Identity\User\Event\PrimaryEmailVerified;
use Galeas\Api\BoundedContext\Identity\User\Event\SignedUp;
use Galeas\Api\Common\Event\Event;
use Galeas\Api\CommonException\ProjectionCannotProcess;
use Galeas\Api\Service\QueueProcessor\EventProjector;

class UserDetailsProjector extends EventProjector
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
                    $this->saveOne(UserDetails::fromUserIdAndEmails(
                        $event->aggregateId()->id(),
                        null,
                        $event->primaryEmail()
                    ));
                    break;
                case $event instanceof PrimaryEmailVerified:
                    $userDetails = $this->getOne(UserDetails::class, ['id' => $event->aggregateId()->id()]);
                    $this->saveOne($userDetails?->verifyEmail());
                    break;
                case $event instanceof PrimaryEmailChangeRequested:
                    $userDetails = $this->getOne(UserDetails::class, ['id' => $event->aggregateId()->id()]);
                    $this->saveOne($userDetails?->requestNewEmail($event->newEmailRequested()));
                    break;
            }
            $this->commitProjection($event, 'Identity_User_UserDetails');
        } catch (\Throwable $throwable) {
            throw new ProjectionCannotProcess($throwable);
        }
    }
}
