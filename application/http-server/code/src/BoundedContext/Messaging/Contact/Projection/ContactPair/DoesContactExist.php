<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Messaging\Contact\Projection\ContactPair;

use Doctrine\ODM\MongoDB\DocumentManager;
use Galeas\Api\BoundedContext\Messaging\Contact\CommandHandler\RequestContact\DoesContactExist as RCDoesContactExist;
use Galeas\Api\Common\ExceptionBase\ProjectionCannotRead;

class DoesContactExist implements RCDoesContactExist
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
    public function doesContactExist(
        string $firstContact,
        string $secondContact
    ): bool {
        try {
            $queryBuilder = $this->projectionDocumentManager
                ->createQueryBuilder(ContactPair::class);

            $contactPair = $queryBuilder
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

            if (null === $contactPair) {
                return false;
            }

            if ($contactPair instanceof ContactPair) {
                return true;
            }

            throw new \Exception();
        } catch (\Throwable $exception) {
            throw new ProjectionCannotRead($exception);
        }
    }
}
