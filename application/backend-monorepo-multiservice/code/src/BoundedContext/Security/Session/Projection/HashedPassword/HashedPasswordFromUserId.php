<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Security\Session\Projection\HashedPassword;

use Doctrine\ODM\MongoDB\DocumentManager;
use Galeas\Api\BoundedContext\Security\Session\CommandHandler\SignIn\HashedPasswordFromUserId as SIHashedPasswordFromUserId;
use Galeas\Api\Common\ExceptionBase\ProjectionCannotRead;

class HashedPasswordFromUserId implements SIHashedPasswordFromUserId
{
    /**
     * @var DocumentManager
     */
    private $projectionDocumentManager;

    public function __construct(DocumentManager $projectionDocumentManager)
    {
        $this->projectionDocumentManager = $projectionDocumentManager;
    }

    public function hashedPasswordFromUserId(string $userId): ?string
    {
        try {
            $queryBuilder = $this->projectionDocumentManager
                ->createQueryBuilder(HashedPassword::class)
                ->field('id')
                ->equals($userId);

            $hashedPassword = $queryBuilder->getQuery()->getSingleResult();

            if ($hashedPassword instanceof HashedPassword) {
                return $hashedPassword->getHashedPassword();
            }

            if (null === $hashedPassword) {
                return null;
            }

            throw new \Exception();
        } catch (\Throwable $exception) {
            throw new ProjectionCannotRead($exception);
        }
    }
}
