<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Messaging\Contact\Projection\ContactList;

use Doctrine\ODM\MongoDB\DocumentManager;
use Galeas\Api\BoundedContext\Identity\User\Event\SignedUp;
use Galeas\Api\Common\Event\Event;
use Galeas\Api\Common\ExceptionBase\ProjectionCannotProcess;
use Galeas\Api\Service\QueueProcessor\ProjectionEventProcessor;

class ContactDetailsProcessor implements ProjectionEventProcessor
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
                $username = $event->username();
            } else {
                return;
            }

            $queryBuilder = $this->projectionDocumentManager
                ->createQueryBuilder(ContactDetails::class);

            $contactDetails = $queryBuilder
                ->field('id')->equals($userId)
                ->getQuery()
                ->getSingleResult();

            if (
                null !== $contactDetails &&
                !($contactDetails instanceof ContactDetails)
            ) {
                throw new \Exception();
            }

            if (null !== $contactDetails) {
                $contactDetails->changeUsername($username);
            } else {
                $contactDetails = ContactDetails::fromUserIdAndUsername(
                    $userId,
                    $username
                );
            }

            $this->projectionDocumentManager->persist($contactDetails);
            $this->projectionDocumentManager->flush();
        } catch (\Throwable $exception) {
            throw new ProjectionCannotProcess($exception);
        }
    }
}
