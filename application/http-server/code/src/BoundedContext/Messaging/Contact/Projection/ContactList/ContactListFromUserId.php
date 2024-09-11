<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Messaging\Contact\Projection\ContactList;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Iterator\Iterator;
use Doctrine\ODM\MongoDB\MongoDBException;
use Galeas\Api\BoundedContext\Messaging\Contact\QueryHandler\ListContacts\ContactListFromUserId as LCContactListFromUserId;
use Galeas\Api\Common\ExceptionBase\ProjectionCannotRead;

class ContactListFromUserId implements LCContactListFromUserId
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
    public function contactListFromUserId(string $userId): array
    {
        try {
            $userIdsToUsernames = [];

            $returnArray = [
                'requestedContacts' => array_values(array_filter(
                    array_map(
                        function (ContactListItem $contactListItem) use (&$userIdsToUsernames): array {
                            if (null === $contactListItem->getPendingContact()) {
                                return [];
                            }

                            $otherUserId = $contactListItem->getPendingContact()->getRequestedContactId();
                            $userIdsToUsernames[$otherUserId] = null;

                            return [
                                'id' => $otherUserId,
                                'username' => null,
                            ];
                        },
                        array_values($this->getRequestedContactsForUserId($userId))
                    ),
                    function (array $contactListItem): bool {
                        if (array_key_exists('id', $contactListItem)) {
                            return true;
                        }

                        return false;
                    }
                )),
                'requestingContacts' => array_values(array_filter(
                    array_map(
                        function (ContactListItem $contactListItem) use (&$userIdsToUsernames): array {
                            if (null === $contactListItem->getPendingContact()) {
                                return [];
                            }

                            $otherUserId = $contactListItem->getPendingContact()->getRequesterContactId();
                            $userIdsToUsernames[$otherUserId] = null;

                            return [
                                'id' => $otherUserId,
                                'username' => null,
                            ];
                        },
                        array_values($this->getRequestingContactsForUserId($userId))
                    ),
                    function (array $contactListItem): bool {
                        if (array_key_exists('id', $contactListItem)) {
                            return true;
                        }

                        return false;
                    }
                )),
                'activeContacts' => array_values(array_filter(
                    array_map(
                        function (ContactListItem $contactListItem) use (&$userIdsToUsernames, $userId): array {
                            if (null === $contactListItem->getActiveContact()) {
                                return [];
                            }

                            if ($contactListItem->getActiveContact()->getFirstContactId() !== $userId) {
                                $otherUserId = $contactListItem->getActiveContact()->getFirstContactId();
                            } else {
                                $otherUserId = $contactListItem->getActiveContact()->getSecondContactId();
                            }
                            $userIdsToUsernames[$otherUserId] = null;

                            return [
                                'id' => $otherUserId,
                                'username' => null,
                            ];
                        },
                        array_values($this->getActiveContactsForUserId($userId))
                    ),

                    function (array $contactListItem): bool {
                        if (array_key_exists('id', $contactListItem)) {
                            return true;
                        }

                        return false;
                    }
                )),
            ];

            $userIdsToUsernames = $this->fillUserIdsToUsernamesValues($userIdsToUsernames);

            foreach ($returnArray['requestedContacts'] as $key => $contact) {
                $returnArray['requestedContacts'][$key]['username'] = $userIdsToUsernames[$contact['id']];
            }
            foreach ($returnArray['requestingContacts'] as $key => $contact) {
                $returnArray['requestingContacts'][$key]['username'] = $userIdsToUsernames[$contact['id']];
            }
            foreach ($returnArray['activeContacts'] as $key => $contact) {
                $returnArray['activeContacts'][$key]['username'] = $userIdsToUsernames[$contact['id']];
            }

            return $returnArray;
        } catch (\Throwable $exception) {
            throw new ProjectionCannotRead($exception);
        }
    }

    /**
     * @throws MongoDBException|\Exception
     */
    private function fillUserIdsToUsernamesValues(array $userIdsToUsernames): array
    {
        $userIds = array_keys($userIdsToUsernames);

        $contactDetailsArray = $this->projectionDocumentManager
            ->createQueryBuilder(ContactDetails::class)
            ->field('id')->in($userIds)
            ->getQuery()
            ->execute();

        if ($contactDetailsArray instanceof Iterator) {
            $contactDetailsArray = $contactDetailsArray->toArray();
        } else {
            throw new \Exception();
        }

        foreach ($contactDetailsArray as $contactDetails) {
            if (!($contactDetails instanceof ContactDetails)) {
                throw new \Exception();
            }
            $userId = $contactDetails->getUserId();

            if (array_key_exists($userId, $userIdsToUsernames)) {
                $userIdsToUsernames[$userId] = $contactDetails->getUsername();
            }
        }

        return $userIdsToUsernames;
    }

    /**
     * @return ContactListItem[]
     *
     * @throws MongoDBException|\Exception
     */
    private function getRequestedContactsForUserId(string $userId): array
    {
        $queryBuilder = $this->projectionDocumentManager
            ->createQueryBuilder(ContactListItem::class);

        $contactListItemArray = $queryBuilder
            ->field('pendingContact.requesterContactId')->equals($userId)
            ->sort('pendingContact.requesterContactId', 'asc')
            ->getQuery()
            ->execute();

        if ($contactListItemArray instanceof Iterator) {
            return $contactListItemArray->toArray();
        }

        throw new \Exception();
    }

    /**
     * @return ContactListItem[]
     *
     * @throws MongoDBException|\Exception
     */
    private function getRequestingContactsForUserId(string $userId): array
    {
        $queryBuilder = $this->projectionDocumentManager
            ->createQueryBuilder(ContactListItem::class);

        $contactListItemArray = $queryBuilder
            ->field('pendingContact.requestedContactId')->equals($userId)
            ->sort('pendingContact.requestedContactId', 'asc')
            ->getQuery()
            ->execute();

        if ($contactListItemArray instanceof Iterator) {
            return $contactListItemArray->toArray();
        }

        throw new \Exception();
    }

    /**
     * @return ContactListItem[]
     *
     * @throws MongoDBException|\Exception
     */
    private function getActiveContactsForUserId(string $userId): array
    {
        $queryBuilder = $this->projectionDocumentManager
            ->createQueryBuilder(ContactListItem::class);

        $contactListItemArray = $queryBuilder
            ->addOr(
                $queryBuilder->expr()->field('activeContact.firstContactId')->equals($userId)
            )
            ->addOr(
                $queryBuilder->expr()->field('activeContact.secondContactId')->equals($userId)
            )
            ->sort('activeContact.firstContactId', 'asc')
            ->sort('activeContact.secondContactId', 'asc')
            ->getQuery()
            ->execute();

        if ($contactListItemArray instanceof Iterator) {
            return $contactListItemArray->toArray();
        }

        throw new \Exception();
    }
}
