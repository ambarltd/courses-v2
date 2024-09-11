<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Messaging\Contact\Projection\ContactPair;

use Galeas\Api\BoundedContext\Messaging\Contact\Projection\ContactPair\ContactIdFromContacts;
use Galeas\Api\BoundedContext\Messaging\Contact\Projection\ContactPair\ContactPair;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\KernelTestBase;

class ContactIdFromContactsTest extends KernelTestBase
{
    /**
     * @test
     *
     * @throws \Exception
     */
    public function testContactIdFromContacts(): void
    {
        $contactIdFromContacts = $this->getContainer()
            ->get(ContactIdFromContacts::class);

        Assert::assertEquals(
            null,
            $contactIdFromContacts->contactIdFromContacts('contact_1', 'contact_2')
        );
        Assert::assertEquals(
            null,
            $contactIdFromContacts->contactIdFromContacts('contact_2', 'contact_3')
        );
        Assert::assertEquals(
            null,
            $contactIdFromContacts->contactIdFromContacts('contact_a', 'contact_b')
        );

        $this->getProjectionDocumentManager()
            ->persist(
                ContactPair::fromProperties(
                    'contact_1_and_contact_2',
                    'contact_1',
                    'contact_2'
                )
            );
        $this->getProjectionDocumentManager()->flush();

        Assert::assertEquals(
            'contact_1_and_contact_2',
            $contactIdFromContacts->contactIdFromContacts('contact_1', 'contact_2')
        );
        Assert::assertEquals(
            null,
            $contactIdFromContacts->contactIdFromContacts('contact_2', 'contact_3')
        );
        Assert::assertEquals(
            null,
            $contactIdFromContacts->contactIdFromContacts('contact_a', 'contact_b')
        );

        $this->getProjectionDocumentManager()
            ->persist(
                ContactPair::fromProperties(
                    'contact_2_and_contact_3',
                    'contact_2',
                    'contact_3'
                )
            );
        $this->getProjectionDocumentManager()->flush();
        Assert::assertEquals(
            'contact_1_and_contact_2',
            $contactIdFromContacts->contactIdFromContacts('contact_1', 'contact_2')
        );
        Assert::assertEquals(
            'contact_2_and_contact_3',
            $contactIdFromContacts->contactIdFromContacts('contact_2', 'contact_3')
        );
        Assert::assertEquals(
            null,
            $contactIdFromContacts->contactIdFromContacts('contact_a', 'contact_b')
        );

        $this->getProjectionDocumentManager()
            ->persist(
                ContactPair::fromProperties(
                    'contact_a_and_contact_b',
                    'contact_a',
                    'contact_b'
                )
            );
        $this->getProjectionDocumentManager()->flush();
        Assert::assertEquals(
            'contact_1_and_contact_2',
            $contactIdFromContacts->contactIdFromContacts('contact_1', 'contact_2')
        );
        Assert::assertEquals(
            'contact_2_and_contact_3',
            $contactIdFromContacts->contactIdFromContacts('contact_2', 'contact_3')
        );
        Assert::assertEquals(
            'contact_a_and_contact_b',
            $contactIdFromContacts->contactIdFromContacts('contact_a', 'contact_b')
        );

        $removeThisPair = $this->getProjectionDocumentManager()
            ->createQueryBuilder(ContactPair::class)
            ->field('id')->equals('contact_2_and_contact_3')
            ->getQuery()
            ->getSingleResult();

        if (false === is_object($removeThisPair)) {
            throw new \Exception();
        }

        $this->getProjectionDocumentManager()->remove($removeThisPair);
        $this->getProjectionDocumentManager()->flush();

        Assert::assertEquals(
            'contact_1_and_contact_2',
            $contactIdFromContacts->contactIdFromContacts('contact_1', 'contact_2')
        );
        Assert::assertEquals(
            null,
            $contactIdFromContacts->contactIdFromContacts('contact_2', 'contact_3')
        );
        Assert::assertEquals(
            'contact_a_and_contact_b',
            $contactIdFromContacts->contactIdFromContacts('contact_a', 'contact_b')
        );
    }
}
