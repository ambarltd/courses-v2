<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Security\Session\Projection\Session;

use Doctrine\ODM\MongoDB\DocumentManager;
use Galeas\Api\BoundedContext\Security\Session\CommandHandler\RefreshToken\SessionIdFromSessionToken as RTSessionIdFromSessionToken;
use Galeas\Api\BoundedContext\Security\Session\CommandHandler\SignOut\SessionIdFromSessionToken as SOSessionIdFromSessionToken;
use Galeas\Api\CommonException\ProjectionCannotRead;

class SessionIdFromSessionToken implements RTSessionIdFromSessionToken, SOSessionIdFromSessionToken
{
    private DocumentManager $projectionDocumentManager;

    public function __construct(DocumentManager $projectionDocumentManager)
    {
        $this->projectionDocumentManager = $projectionDocumentManager;
    }

    public function sessionIdFromSessionToken(string $sessionToken): ?string
    {
        try {
            $session = $this->projectionDocumentManager
                ->createQueryBuilder(Session::class)
                ->field('sessionToken')->equals($sessionToken)
                ->getQuery()
                ->getSingleResult()
            ;

            if ($session instanceof Session) {
                return $session->getSessionId();
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
