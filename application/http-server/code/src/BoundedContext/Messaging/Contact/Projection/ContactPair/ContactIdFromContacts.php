<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Messaging\Contact\Projection\ContactPair;

use Doctrine\ODM\MongoDB\DocumentManager;
use Galeas\Api\BoundedContext\Messaging\Contact\CommandHandler\AcceptContactRequest\ContactIdFromContacts as ACRContactIdFromContacts;
use Galeas\Api\BoundedContext\Messaging\Contact\CommandHandler\CancelContactRequest\ContactIdFromContacts as CCRContactIdFromContacts;
use Galeas\Api\BoundedContext\Messaging\Contact\CommandHandler\DeleteContact\ContactIdFromContacts as DCContactIdFromContacts;
use Galeas\Api\BoundedContext\Messaging\Contact\CommandHandler\RejectContactRequest\ContactIdFromContacts as RCRContactIdFromContacts;
use Galeas\Api\BoundedContext\Messaging\Contact\CommandHandler\RequestContact\ContactIdFromContacts as RCContactIdFromContacts;
use Galeas\Api\Common\ExceptionBase\ProjectionCannotRead;

class ContactIdFromContacts implements ACRContactIdFromContacts, RCRContactIdFromContacts, CCRContactIdFromContacts, DCContactIdFromContacts, RCContactIdFromContacts
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
    public function contactIdFromContacts(
        string $firstContact,
        string $secondContact
    ): ?string {
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

            if (
                null !== $contactPair &&
                !($contactPair instanceof ContactPair)
            ) {
                throw new \Exception();
            }

            if (null !== $contactPair) {
                return $contactPair->getContactId();
            }

            return null;
        } catch (\Throwable $exception) {
            throw new ProjectionCannotRead($exception);
        }
    }
}
