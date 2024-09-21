<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Security\Session\Projection\Session;

use Doctrine\ODM\MongoDB\DocumentManager;
use Galeas\Api\Common\ExceptionBase\ProjectionCannotRead;

class UserIdFromSignedInSessionToken
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
     * @throws ProjectionCannotRead
     */
    public function userIdFromSignedInSessionToken(
        string $sessionToken,
        \DateTimeImmutable $withTokenRefreshedAfterDate
    ): ?string {
        try {
            $session = $this->projectionDocumentManager
                ->createQueryBuilder(Session::class)
                ->field('sessionToken')->equals($sessionToken)
                ->getQuery()
                ->getSingleResult();

            if (
                $session instanceof Session &&
                $session->getTokenLastRefreshedAt() > $withTokenRefreshedAfterDate &&
                false === $session->isSignedOut()
            ) {
                return $session->getUserId();
            }

            if ($session instanceof Session) {
                return null;
            }

            if (null === $session) {
                return null;
            }

            throw new \Exception();
        } catch (\Throwable $exception) {
            throw new ProjectionCannotRead($exception);
        }
    }
}
