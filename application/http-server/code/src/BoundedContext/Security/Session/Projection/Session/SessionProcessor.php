<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Security\Session\Projection\Session;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use Galeas\Api\BoundedContext\Security\Session\Event\SignedIn;
use Galeas\Api\BoundedContext\Security\Session\Event\SignedOut;
use Galeas\Api\BoundedContext\Security\Session\Event\TokenRefreshed;
use Galeas\Api\Common\Event\Event;
use Galeas\Api\Common\ExceptionBase\ProjectionCannotProcess;
use Galeas\Api\Service\QueueProcessor\ProjectionEventProcessor;

class SessionProcessor implements ProjectionEventProcessor
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
            if ($event instanceof SignedIn) {
                $session = $this->getSessionById(
                    $event->aggregateId()->id()
                );

                if ($session instanceof Session) {
                    $session->changeProperties(
                        $event->asUser()->id(),
                        $event->sessionTokenCreated(),
                        false,
                        $event->eventOccurredOn()
                    );
                } else {
                    $session = Session::fromProperties(
                        $event->aggregateId()->id(),
                        $event->asUser()->id(),
                        $event->sessionTokenCreated(),
                        false,
                        $event->eventOccurredOn()
                    );
                }

                $this->persistAndFlushSession($session);
            } elseif ($event instanceof TokenRefreshed) {
                $session = $this->getSessionById(
                    $event->aggregateId()->id()
                );

                if ($session instanceof Session) {
                    $session->changeProperties(
                        $session->getUserId(),
                        $event->refreshedSessionToken(),
                        false,
                        $event->eventOccurredOn()
                    );
                } else {
                    $session = Session::fromProperties(
                        $event->aggregateId()->id(),
                        null,
                        $event->refreshedSessionToken(),
                        false,
                        $event->eventOccurredOn()
                    );
                }

                $this->persistAndFlushSession($session);
            } elseif ($event instanceof SignedOut) {
                $session = $this->getSessionById($event->aggregateId()->id());

                if ($session instanceof Session) {
                    $session->changeProperties(
                        $session->getUserId(),
                        $event->withSessionToken(),
                        true,
                        $session->getTokenLastRefreshedAt()
                    );
                } else {
                    $session = Session::fromProperties(
                        $event->aggregateId()->id(),
                        null,
                        $event->withSessionToken(),
                        true,
                        $event->eventOccurredOn()
                    );
                }

                $this->persistAndFlushSession($session);
            }
        } catch (\Throwable $exception) {
            throw new ProjectionCannotProcess($exception);
        }
    }

    /**
     * @throws \InvalidArgumentException|MongoDBException
     */
    private function persistAndFlushSession(Session $session): void
    {
        $this->projectionDocumentManager->persist($session);
        $this->projectionDocumentManager->flush();
    }

    /**
     * @throws \Exception
     */
    private function getSessionById(string $sessionId): ?Session
    {
        try {
            $session = $this->projectionDocumentManager
                ->createQueryBuilder(Session::class)
                ->field('id')->equals($sessionId)
                ->getQuery()
                ->getSingleResult();

            if (
                null === $session ||
                $session instanceof Session
            ) {
                return $session;
            }

            throw new \Exception();
        } catch (\Throwable $exception) {
            throw new ProjectionCannotProcess($exception);
        }
    }
}
