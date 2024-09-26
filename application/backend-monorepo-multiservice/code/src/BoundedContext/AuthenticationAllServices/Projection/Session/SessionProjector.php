<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\AuthenticationAllServices\Projection\Session;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use Galeas\Api\BoundedContext\Security\Session\Event\SignedIn;
use Galeas\Api\BoundedContext\Security\Session\Event\SignedOut;
use Galeas\Api\BoundedContext\Security\Session\Event\TokenRefreshed;
use Galeas\Api\Common\Event\Event;
use Galeas\Api\CommonException\ProjectionCannotProcess;
use Galeas\Api\Service\QueueProcessor\EventProjector;

class SessionProjector implements EventProjector
{
    private DocumentManager $projectionDocumentManager;

    public function __construct(DocumentManager $projectionDocumentManager)
    {
        $this->projectionDocumentManager = $projectionDocumentManager;
    }

    public function project(Event $event): void
    {
        try {
            if ($event instanceof SignedIn) {
                $session = Session::fromProperties(
                    $event->aggregateId()->id(),
                    $event->asUser()->id(),
                    $event->sessionTokenCreated(),
                    false,
                    $event->recordedOn()
                );

                $this->persistAndFlushSession($session);
            } elseif ($event instanceof TokenRefreshed) {
                $session = $this->getSessionById(
                    $event->aggregateId()->id()
                );

                if ($session instanceof Session) {
                    $session->changeProperties(
                        $event->refreshedSessionToken(),
                        false,
                        $event->recordedOn()
                    );
                } else {
                    return;
                }

                $this->persistAndFlushSession($session);
            } elseif ($event instanceof SignedOut) {
                $session = $this->getSessionById($event->aggregateId()->id());

                if ($session instanceof Session) {
                    $session->changeProperties(
                        $session->getSessionToken(),
                        true,
                        $session->getTokenLastRefreshedAt()
                    );
                } else {
                    return;
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
                ->getSingleResult()
            ;

            if (
                null === $session
                || $session instanceof Session
            ) {
                return $session;
            }

            throw new \Exception();
        } catch (\Throwable $exception) {
            throw new ProjectionCannotProcess($exception);
        }
    }
}
