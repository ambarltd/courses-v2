<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Security\Session\Projection\HashedPassword;

use Doctrine\ODM\MongoDB\DocumentManager;
use Galeas\Api\BoundedContext\Identity\User\Event\SignedUp;
use Galeas\Api\Common\Event\Event;
use Galeas\Api\Common\ExceptionBase\ProjectionCannotProcess;
use Galeas\Api\Service\QueueProcessor\EventProjector;

class HashedPasswordProjector implements EventProjector
{
    private DocumentManager $projectionDocumentManager;

    public function __construct(DocumentManager $projectionDocumentManager)
    {
        $this->projectionDocumentManager = $projectionDocumentManager;
    }

    public function project(Event $event): void
    {
        try {
            if (!($event instanceof SignedUp)) {
                return;
            }

            $userId = $event->aggregateId()->id();
            $hashedPassword = $event->hashedPassword();

            $queryBuilder = $this->projectionDocumentManager
                ->createQueryBuilder(HashedPassword::class)
                ->field('id')
                ->equals($userId);

            $hashedPasswordObject = $queryBuilder->getQuery()->getSingleResult();

            if ($hashedPasswordObject instanceof HashedPassword) {
                $hashedPasswordObject->changeHashedPassword($hashedPassword);
                $this->projectionDocumentManager->persist($hashedPasswordObject);
                $this->projectionDocumentManager->flush();
            } elseif (null === $hashedPasswordObject) {
                $hashedPasswordObject = HashedPassword::fromUserIdAndHashedPassword(
                    $event->aggregateId()->id(),
                    $event->hashedPassword()
                );
                $this->projectionDocumentManager->persist($hashedPasswordObject);
                $this->projectionDocumentManager->flush();
            } else {
                throw new \Exception();
            }
        } catch (\Throwable $exception) {
            throw new ProjectionCannotProcess($exception);
        }
    }
}
