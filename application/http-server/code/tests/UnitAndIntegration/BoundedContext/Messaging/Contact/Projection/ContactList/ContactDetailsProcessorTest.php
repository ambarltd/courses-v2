<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Messaging\Contact\Projection\ContactList;

use Galeas\Api\BoundedContext\Identity\User\Event\SignedUp;
use Galeas\Api\BoundedContext\Messaging\Contact\Projection\ContactList\ContactDetails;
use Galeas\Api\BoundedContext\Messaging\Contact\Projection\ContactList\ContactDetailsProcessor;
use Tests\Galeas\Api\UnitAndIntegration\KernelTestBase;

class ContactDetailsProcessorTest extends KernelTestBase
{
    /**
     * @test
     */
    public function testContactDetailsProcessor(): void
    {
        $contactDetailsProcessor = $this->getContainer()
            ->get(ContactDetailsProcessor::class);

        $signedUp1 = SignedUp::fromProperties(
            [],
            'test1@example.com',
            'password_test_123',
            'useRname_Test',
            false
        );
        $userId1 = $signedUp1->aggregateId()->id();
        $contactDetailsProcessor->process($signedUp1);
        $contactDetailsProcessor->process($signedUp1); // test idempotency

        $this->assertEquals(
            [
                ContactDetails::fromUserIdAndUsername(
                    $userId1,
                    $signedUp1->username()
                ),
            ],
            $this->findAllContactDetails()
        );

        $signedUp2 = SignedUp::fromProperties(
            [],
            'test2@example.com',
            'password_test_2_123',
            'useRname_2_Test',
            false
        );
        $userId2 = $signedUp2->aggregateId()->id();
        $contactDetailsProcessor->process($signedUp2);

        $this->assertEquals(
            [
                ContactDetails::fromUserIdAndUsername(
                    $userId1,
                    $signedUp1->username()
                ),
                ContactDetails::fromUserIdAndUsername(
                    $userId2,
                    $signedUp2->username()
                ),
            ],
            $this->findAllContactDetails()
        );
    }

    /**
     * @return ContactDetails[]
     */
    private function findAllContactDetails()
    {
        return array_values(
            $this->getProjectionDocumentManager()
                ->createQueryBuilder(ContactDetails::class)
                ->sort('username', 'desc')
                ->getQuery()
                ->execute()
                ->toArray()
        );
    }
}
