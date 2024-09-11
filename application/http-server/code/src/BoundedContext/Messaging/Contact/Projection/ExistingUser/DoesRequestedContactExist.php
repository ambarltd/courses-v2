<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Messaging\Contact\Projection\ExistingUser;

use Doctrine\ODM\MongoDB\DocumentManager;
use Galeas\Api\BoundedContext\Messaging\Contact\CommandHandler\RequestContact\DoesRequestedContactExist as RCDoesRequestedContactExist;
use Galeas\Api\Common\ExceptionBase\ProjectionCannotRead;

class DoesRequestedContactExist implements RCDoesRequestedContactExist
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
    public function doesRequestedContactExist(string $requestedContact): bool
    {
        try {
            $queryBuilder = $this->projectionDocumentManager
                ->createQueryBuilder(ExistingUser::class);

            $existingUser = $queryBuilder
                ->field('id')->equals($requestedContact)
                ->getQuery()
                ->getSingleResult();

            if ($existingUser instanceof ExistingUser) {
                return true;
            }

            if (null === $existingUser) {
                return false;
            }

            throw new \Exception();
        } catch (\Throwable $exception) {
            throw new ProjectionCannotRead($exception);
        }
    }
}
