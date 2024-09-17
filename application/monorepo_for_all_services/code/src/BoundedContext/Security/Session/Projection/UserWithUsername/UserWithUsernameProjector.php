<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Security\Session\Projection\UserWithUsername;

use Doctrine\ODM\MongoDB\DocumentManager;
use Galeas\Api\BoundedContext\Identity\User\Event\SignedUp;
use Galeas\Api\Common\Event\Event;
use Galeas\Api\Common\ExceptionBase\ProjectionCannotProcess;
use Galeas\Api\Service\QueueProcessor\EventProjector;

class UserWithUsernameProjector implements EventProjector
{
    private DocumentManager $projectionDocumentManager;

    public function __construct(DocumentManager $projectionDocumentManager)
    {
        $this->projectionDocumentManager = $projectionDocumentManager;
    }

    public function project(Event $event): void
    {
        try {
            $userId = null;
            $username = null;

            if ($event instanceof SignedUp) {
                $userId = $event->aggregateId()->id();
                $username = $event->username();
            }

            if (
                null === $userId ||
                null === $username
            ) {
                return;
            }

            $userWithUsername = $this->projectionDocumentManager
                ->createQueryBuilder(UserWithUsername::class)
                ->field('id')->equals($userId)
                ->getQuery()
                ->getSingleResult();

            if ($userWithUsername instanceof UserWithUsername) {
                $userWithUsername->changeUsername($username);
            } elseif (null === $userWithUsername) {
                $userWithUsername = UserWithUsername::fromProperties(
                    $username,
                    $userId
                );
            } else {
                throw new \Exception();
            }

            $this->projectionDocumentManager->persist($userWithUsername);
            $this->projectionDocumentManager->flush();
        } catch (\Throwable $exception) {
            throw new ProjectionCannotProcess($exception);
        }
    }
}
