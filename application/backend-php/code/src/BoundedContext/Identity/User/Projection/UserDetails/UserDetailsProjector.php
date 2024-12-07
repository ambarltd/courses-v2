<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\User\Projection\UserDetails;

use Doctrine\ODM\MongoDB\DocumentManager;
use Galeas\Api\BoundedContext\Identity\User\Event\PrimaryEmailChangeRequested;
use Galeas\Api\BoundedContext\Identity\User\Event\PrimaryEmailVerified;
use Galeas\Api\BoundedContext\Identity\User\Event\SignedUp;
use Galeas\Api\Common\Event\Event;
use Galeas\Api\Service\QueueProcessor\EventProjector;

class UserDetailsProjector extends EventProjector
{
    public function __construct(DocumentManager $projectionDocumentManager)
    {
        $this->projectionDocumentManager = $projectionDocumentManager;
    }

    protected function project(Event $event): void
    {
        switch (true) {
            case $event instanceof SignedUp:
                $this->saveOne(UserDetails::fromUserIdUsernameAndEmails(
                    $event->aggregateId()->id(),
                    $event->username(),
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
    }
}
