<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Security\Session\Projection\UserWithUsername;

use Galeas\Api\BoundedContext\Identity\User\Event\SignedUp;
use Galeas\Api\BoundedContext\Security\Session\Projection\UserWithUsername\UserWithUsername;
use Galeas\Api\BoundedContext\Security\Session\Projection\UserWithUsername\UserWithUsernameProcessor;
use Tests\Galeas\Api\UnitAndIntegration\KernelTestBase;

class UserWithUsernameProcessorTest extends KernelTestBase
{
    /**
     * @test
     */
    public function testSessionProcessor(): void
    {
        $userWithUsernameProcessor = $this->getContainer()
            ->get(UserWithUsernameProcessor::class);

        $signedUp1 = SignedUp::fromProperties(
            [],
            'Email_1_a@example.com',
            'password',
            'uSername',
            true
        );
        $signedUp2 = SignedUp::fromProperties(
            [],
            'Email_2_a@@example.com',
            'password_2',
            'uSername_2',
            true
        );

        // EMPTY

        $userWithUsernameArray1 = $this->findUsersById(
            $signedUp1->aggregateId()->id()
        );
        $userWithUsernameArray2 = $this->findUsersById(
            $signedUp2->aggregateId()->id()
        );
        $this->assertCount(
            0,
            $userWithUsernameArray1
        );
        $this->assertCount(
            0,
            $userWithUsernameArray2
        );

        // SignedUp 1

        $userWithUsernameProcessor->process($signedUp1);
        $userWithUsernameProcessor->process($signedUp1); // test idempotency

        $userWithUsernameArray1 = $this->findUsersById(
            $signedUp1->aggregateId()->id()
        );
        $userWithUsernameArray2 = $this->findUsersById(
            $signedUp2->aggregateId()->id()
        );
        $this->assertCount(
            1,
            $userWithUsernameArray1
        );
        $this->assertEquals(
            $signedUp1->aggregateId()->id(),
            $userWithUsernameArray1[0]->getUserId()
        );
        $this->assertEquals(
            strtolower($signedUp1->username()),
            $userWithUsernameArray1[0]->getCanonicalUsername()
        );
        $this->assertCount(
            0,
            $userWithUsernameArray2
        );

        // SignedUp 2

        $userWithUsernameProcessor->process($signedUp2);
        $userWithUsernameProcessor->process($signedUp2); // test idempotency

        $userWithUsernameArray1 = $this->findUsersById(
            $signedUp1->aggregateId()->id()
        );
        $userWithUsernameArray2 = $this->findUsersById(
            $signedUp2->aggregateId()->id()
        );
        $this->assertCount(
            1,
            $userWithUsernameArray1
        );
        $this->assertEquals(
            $signedUp1->aggregateId()->id(),
            $userWithUsernameArray1[0]->getUserId()
        );
        $this->assertEquals(
            strtolower($signedUp1->username()),
            $userWithUsernameArray1[0]->getCanonicalUsername()
        );
        $this->assertCount(
            1,
            $userWithUsernameArray2
        );
        $this->assertEquals(
            $signedUp2->aggregateId()->id(),
            $userWithUsernameArray2[0]->getUserId()
        );
        $this->assertEquals(
            strtolower($signedUp2->username()),
            $userWithUsernameArray2[0]->getCanonicalUsername()
        );
    }

    /**
     * @return UserWithUsername[]
     */
    private function findUsersById(string $userId): array
    {
        return array_values(
            $this->getProjectionDocumentManager()
                ->createQueryBuilder(UserWithUsername::class)
                ->field('id')->equals($userId)
                ->getQuery()
                ->execute()
                ->toArray()
        );
    }
}
