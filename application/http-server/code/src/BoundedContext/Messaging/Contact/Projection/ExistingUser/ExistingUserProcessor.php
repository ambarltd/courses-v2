<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Messaging\Contact\Projection\ExistingUser;

use Doctrine\ODM\MongoDB\DocumentManager;
use Galeas\Api\BoundedContext\Identity\User\Event\SignedUp;
use Galeas\Api\Common\Event\Event;
use Galeas\Api\Common\ExceptionBase\ProjectionCannotProcess;
use Galeas\Api\Service\QueueProcessor\ProjectionEventProcessor;

class ExistingUserProcessor implements ProjectionEventProcessor
{
    /**
     * @var DocumentManager
     */
    private $projectionDocumentManager;

    public function __construct(DocumentManager $projectionDocumentManager)
    {
        $this->projectionDocumentManager = $projectionDocumentManager;
    }

    /**
     * {@inheritdoc}
     */
    public function process(Event $event): void
    {
        try {
            if ($event instanceof SignedUp) {
                $userId = $event->aggregateId()->id();
            } else {
                return;
            }

            $queryBuilder = $this->projectionDocumentManager
                ->createQueryBuilder(ExistingUser::class)
                ->field('id')->equals($userId);

            $existingUser = $queryBuilder->getQuery()->getSingleResult();

            if (null === $existingUser) {
                $this->projectionDocumentManager->persist(
                    ExistingUser::fromUserId($userId)
                );
                $this->projectionDocumentManager->flush();
            } elseif ($existingUser instanceof ExistingUser) {
                return;
            } else {
                throw new \Exception();
            }
        } catch (\Throwable $exception) {
            throw new ProjectionCannotProcess($exception);
        }
    }
}
