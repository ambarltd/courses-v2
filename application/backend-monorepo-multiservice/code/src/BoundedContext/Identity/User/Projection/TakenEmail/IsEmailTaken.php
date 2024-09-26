<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\User\Projection\TakenEmail;

use Doctrine\ODM\MongoDB\DocumentManager;
use Galeas\Api\CommonException\ProjectionCannotRead;

class IsEmailTaken
{
    private DocumentManager $projectionDocumentManager;

    public function __construct(DocumentManager $projectionDocumentManager)
    {
        $this->projectionDocumentManager = $projectionDocumentManager;
    }

    /**
     * @throws ProjectionCannotRead
     */
    public function isEmailTaken(string $email): bool
    {
        try {
            $queryBuilder = $this->projectionDocumentManager
                ->createQueryBuilder(TakenEmail::class)
            ;

            $queryBuilder->addOr(
                $queryBuilder->expr()
                    ->field('canonicalVerifiedEmail')
                    ->equals(strtolower($email))
            );
            $queryBuilder->addOr(
                $queryBuilder->expr()
                    ->field('canonicalRequestedEmail')
                    ->equals(strtolower($email))
            );

            $takenEmail = $queryBuilder->getQuery()->getSingleResult();

            if ($takenEmail instanceof TakenEmail) {
                return true;
            }

            if (null === $takenEmail) {
                return false;
            }

            throw new \Exception();
        } catch (\Throwable $exception) {
            throw new ProjectionCannotRead($exception);
        }
    }
}
