<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Messaging\Contact\Projection\ContactList;

use Galeas\Api\BoundedContext\Messaging\Contact\Projection\ContactList\ActiveContact;
use Galeas\Api\BoundedContext\Messaging\Contact\Projection\ContactList\ContactDetails;
use Galeas\Api\BoundedContext\Messaging\Contact\Projection\ContactList\ContactListFromUserId;
use Galeas\Api\BoundedContext\Messaging\Contact\Projection\ContactList\ContactListItem;
use Galeas\Api\BoundedContext\Messaging\Contact\Projection\ContactList\InactiveContact;
use Galeas\Api\BoundedContext\Messaging\Contact\Projection\ContactList\PendingContact;
use Tests\Galeas\Api\UnitAndIntegration\KernelTestBase;

class ContactListFromUserIdTest extends KernelTestBase
{
    public function testContactListFromUserId(): void
    {
        // blank database
        $contactListFromUserId = $this->getContainer()
            ->get(ContactListFromUserId::class);
        $this->assertEquals(
            [
                'requestedContacts' => [],
                'requestingContacts' => [],
                'activeContacts' => [],
            ],
            $contactListFromUserId->contactListFromUserId('jane_user_id')
        );

        // save contact details

        $jane = ContactDetails::fromUserIdAndUsername(
            'jane_user_id',
            'jane_username'
        );
        $fred = ContactDetails::fromUserIdAndUsername(
            'fred_user_id',
            'fred_username'
        );
        $larry = ContactDetails::fromUserIdAndUsername(
            'larry_user_id',
            'larry_username'
        );
        $joyce = ContactDetails::fromUserIdAndUsername(
            'joyce_user_id',
            'joyce_username'
        );
        $samantha = ContactDetails::fromUserIdAndUsername(
            'samantha_user_id',
            'samantha_username'
        );
        $peter = ContactDetails::fromUserIdAndUsername(
            'peter_user_id',
            'peter_username'
        );

        $janice = ContactDetails::fromUserIdAndUsername(
            'janice_user_id',
            'janice_username'
        );

        $this->getProjectionDocumentManager()->persist($jane);
        $this->getProjectionDocumentManager()->persist($fred);
        $this->getProjectionDocumentManager()->persist($larry);
        $this->getProjectionDocumentManager()->persist($joyce);
        $this->getProjectionDocumentManager()->persist($samantha);
        $this->getProjectionDocumentManager()->persist($peter);
        $this->getProjectionDocumentManager()->persist($janice);

        // jane's contacts
        $this->getProjectionDocumentManager()->persist(
            ContactListItem::fromPendingContact(
                PendingContact::fromContacts(
                    'jane_user_id',
                    'fred_user_id'
                )
            )
        );
        $this->getProjectionDocumentManager()->persist(
            ContactListItem::fromPendingContact(
                PendingContact::fromContacts(
                    'larry_user_id',
                    'jane_user_id'
                )
            )
        );
        $this->getProjectionDocumentManager()->persist(
            ContactListItem::fromActiveContact(
                ActiveContact::fromContacts(
                    'jane_user_id',
                    'joyce_user_id'
                )
            )
        );
        $this->getProjectionDocumentManager()->persist(
            ContactListItem::fromActiveContact(
                ActiveContact::fromContacts(
                    'samantha_user_id',
                    'jane_user_id'
                )
            )
        );
        $this->getProjectionDocumentManager()->persist(
            ContactListItem::fromInactiveContact(
                InactiveContact::fromContacts(
                    'jane_user_id',
                    'peter_user_id'
                )
            )
        );

        // janice's contacts
        $this->getProjectionDocumentManager()->persist(
            ContactListItem::fromPendingContact(
                PendingContact::fromContacts(
                    'janice_user_id',
                    'peter_user_id'
                )
            )
        );
        $this->getProjectionDocumentManager()->persist(
            ContactListItem::fromPendingContact(
                PendingContact::fromContacts(
                    'samantha_user_id',
                    'janice_user_id'
                )
            )
        );
        $this->getProjectionDocumentManager()->persist(
            ContactListItem::fromActiveContact(
                ActiveContact::fromContacts(
                    'janice_user_id',
                    'joyce_user_id'
                )
            )
        );
        $this->getProjectionDocumentManager()->persist(
            ContactListItem::fromActiveContact(
                ActiveContact::fromContacts(
                    'larry_user_id',
                    'janice_user_id'
                )
            )
        );
        $this->getProjectionDocumentManager()->persist(
            ContactListItem::fromInactiveContact(
                InactiveContact::fromContacts(
                    'janice_user_id',
                    'fred_user_id'
                )
            )
        );

        $this->getProjectionDocumentManager()->flush();

        $this->assertEquals(
            [
                'requestedContacts' => [
                    [
                        'id' => 'larry_user_id',
                        'username' => 'larry_username',
                    ],
                ],
                'requestingContacts' => [
                    [
                        'id' => 'fred_user_id',
                        'username' => 'fred_username',
                    ],
                ],
                'activeContacts' => [
                    [
                        'id' => 'joyce_user_id',
                        'username' => 'joyce_username',
                    ],
                    [
                        'id' => 'samantha_user_id',
                        'username' => 'samantha_username',
                    ],
                ],
            ],
            $contactListFromUserId->contactListFromUserId('jane_user_id')
        );
        $this->assertEquals(
            [
                'requestedContacts' => [
                    [
                        'id' => 'samantha_user_id',
                        'username' => 'samantha_username',
                    ],
                ],
                'requestingContacts' => [
                    [
                        'id' => 'peter_user_id',
                        'username' => 'peter_username',
                    ],
                ],
                'activeContacts' => [
                    [
                        'id' => 'joyce_user_id',
                        'username' => 'joyce_username',
                    ],
                    [
                        'id' => 'larry_user_id',
                        'username' => 'larry_username',
                    ],
                ],
            ],
            $contactListFromUserId->contactListFromUserId('janice_user_id')
        );
    }
}
