<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Messaging\OneToOneConversation\Projection\ContactPair;

use Doctrine\ODM\MongoDB\DocumentManager;
use Galeas\Api\BoundedContext\Messaging\OneToOneConversation\CommandHandler\StartOneToOneConversation\AreParticipantsContacts as SCAreParticipantsContacts;
use Galeas\Api\Common\ExceptionBase\ProjectionCannotRead;

class AreParticipantsContacts implements SCAreParticipantsContacts
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
    public function areParticipantsContacts(
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
                        ->addAnd(
                            $queryBuilder->expr()->field('active')->equals(true)
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
                        ->addAnd(
                            $queryBuilder->expr()->field('active')->equals(true)
                        )
                )
                ->getQuery()
                ->getSingleResult();

            if ($contactPair instanceof ContactPair) {
                return true;
            }

            if (null === $contactPair) {
                return false;
            }

            throw new \Exception();
        } catch (\Throwable $exception) {
            throw new ProjectionCannotRead($exception);
        }
    }
}
