<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Security\Session\Projection\UserWithUsername;

use Doctrine\ODM\MongoDB\DocumentManager;
use Galeas\Api\BoundedContext\Security\Session\CommandHandler\SignIn\UserIdFromSignInUsername as SIUserIdFromUsername;
use Galeas\Api\Common\ExceptionBase\ProjectionCannotRead;

class UserIdFromSignInUsername implements SIUserIdFromUsername
{
    /**
     * @var DocumentManager
     */
    private $projectionDocumentManager;

    public function __construct(DocumentManager $projectionDocumentManager)
    {
        $this->projectionDocumentManager = $projectionDocumentManager;
    }

    public function userIdFromSignInUsername(string $username): ?string
    {
        try {
            $userWithUsername = $this->projectionDocumentManager
                ->createQueryBuilder(UserWithUsername::class)
                ->field('canonicalUsername')->equals(strtolower($username))
                ->getQuery()
                ->getSingleResult();

            if ($userWithUsername instanceof UserWithUsername) {
                return $userWithUsername->getUserId();
            }

            if (null === $userWithUsername) {
                return null;
            }

            throw new \Exception();
        } catch (\Throwable $exception) {
            throw new ProjectionCannotRead($exception);
        }
    }
}
