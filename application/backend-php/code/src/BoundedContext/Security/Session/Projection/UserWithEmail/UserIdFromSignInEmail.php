<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Security\Session\Projection\UserWithEmail;

use Doctrine\ODM\MongoDB\DocumentManager;
use Galeas\Api\CommonException\ProjectionCannotRead;

class UserIdFromSignInEmail
{
    private DocumentManager $projectionDocumentManager;

    public function __construct(DocumentManager $projectionDocumentManager)
    {
        $this->projectionDocumentManager = $projectionDocumentManager;
    }

    /**
     * @throws ProjectionCannotRead
     */
    public function userIdFromSignInEmail(string $email): ?string
    {
        try {
            $userWithEmail = $this->projectionDocumentManager
                ->createQueryBuilder(UserWithEmail::class)
                ->field('lowercaseVerifiedEmail')->equals(strtolower($email))
                ->getQuery()
                ->getSingleResult()
            ;

            if ($userWithEmail instanceof UserWithEmail) {
                return $userWithEmail->userId();
            }

            return null;
        } catch (\Throwable $exception) {
            throw new ProjectionCannotRead($exception);
        }
    }
}
