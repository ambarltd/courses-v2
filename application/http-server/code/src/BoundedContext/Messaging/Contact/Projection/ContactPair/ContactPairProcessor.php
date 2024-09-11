<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Messaging\Contact\Projection\ContactPair;

use Doctrine\ODM\MongoDB\DocumentManager;
use Galeas\Api\BoundedContext\Messaging\Contact\Event\ContactDeleted;
use Galeas\Api\BoundedContext\Messaging\Contact\Event\ContactRequestAccepted;
use Galeas\Api\BoundedContext\Messaging\Contact\Event\ContactRequestCancelled;
use Galeas\Api\BoundedContext\Messaging\Contact\Event\ContactRequested;
use Galeas\Api\BoundedContext\Messaging\Contact\Event\ContactRequestedAgain;
use Galeas\Api\BoundedContext\Messaging\Contact\Event\ContactRequestRejected;
use Galeas\Api\Common\Event\Event;
use Galeas\Api\Common\ExceptionBase\ProjectionCannotProcess;
use Galeas\Api\Service\QueueProcessor\ProjectionEventProcessor;

class ContactPairProcessor implements ProjectionEventProcessor
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
            if ($event instanceof ContactRequested) {
                $aggregateId = $event->aggregateId()->id();
                $firstContact = $event->requestedContact()->id();
                $secondContact = $event->requesterContact()->id();
            } elseif ($event instanceof ContactRequestRejected) {
                $aggregateId = $event->aggregateId()->id();
                $firstContact = $event->rejectedContact()->id();
                $secondContact = $event->rejecterContact()->id();
            } elseif ($event instanceof ContactRequestAccepted) {
                $aggregateId = $event->aggregateId()->id();
                $firstContact = $event->acceptedContact()->id();
                $secondContact = $event->accepterContact()->id();
            } elseif ($event instanceof ContactRequestCancelled) {
                $aggregateId = $event->aggregateId()->id();
                $firstContact = $event->cancelledContact()->id();
                $secondContact = $event->cancellerContact()->id();
            } elseif ($event instanceof ContactDeleted) {
                $aggregateId = $event->aggregateId()->id();
                $firstContact = $event->deletedContact()->id();
                $secondContact = $event->deleterContact()->id();
            } elseif ($event instanceof ContactRequestedAgain) {
                $aggregateId = $event->aggregateId()->id();
                $firstContact = $event->requestedContact()->id();
                $secondContact = $event->requesterContact()->id();
            } else {
                return;
            }

            $queryBuilder = $this->projectionDocumentManager
                ->createQueryBuilder(ContactPair::class);

            $contactPair = $queryBuilder
                ->addOr(
                    $queryBuilder->expr()
                        ->addAnd(
                            $queryBuilder->expr()->field('id')->equals($aggregateId)
                        )
                )
                ->addOr(
                    $queryBuilder->expr()
                        ->addAnd(
                            $queryBuilder->expr()->field('firstContactId')->equals($firstContact)
                        )
                        ->addAnd(
                            $queryBuilder->expr()->field('secondContactId')->equals($secondContact)
                        )
                )
                ->addOr(
                    $queryBuilder->expr()
                        ->addAnd(
                            $queryBuilder->expr()->field('secondContactId')->equals($firstContact)
                        )
                        ->addAnd(
                            $queryBuilder->expr()->field('firstContactId')->equals($secondContact)
                        )
                )
                ->getQuery()
                ->getSingleResult();

            if (
                null !== $contactPair &&
                !($contactPair instanceof ContactPair)
            ) {
                throw new \Exception();
            }

            if (null === $contactPair) {
                $contactPair = ContactPair::fromProperties(
                    $aggregateId,
                    $firstContact,
                    $secondContact
                );
            } else {
                $contactPair->changeProperties(
                    $aggregateId,
                    $firstContact,
                    $secondContact
                );
            }

            $this->projectionDocumentManager->persist($contactPair);
            $this->projectionDocumentManager->flush();
        } catch (\Throwable $exception) {
            throw new ProjectionCannotProcess($exception);
        }
    }
}
