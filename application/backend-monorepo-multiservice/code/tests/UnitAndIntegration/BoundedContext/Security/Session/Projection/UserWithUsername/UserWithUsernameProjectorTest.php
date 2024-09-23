<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Security\Session\Projection\UserWithUsername;

use Galeas\Api\BoundedContext\Security\Session\Projection\UserWithUsername\UserWithUsername;
use Galeas\Api\BoundedContext\Security\Session\Projection\UserWithUsername\UserWithUsernameProjector;
use Tests\Galeas\Api\UnitAndIntegration\ProjectionAndReactionIntegrationTest;
use Tests\Galeas\Api\UnitAndIntegration\Util\SampleEvents;

class UserWithUsernameProjectorTest extends ProjectionAndReactionIntegrationTest
{
    public function testSessionProjector(): void
    {
        $UserWithUsernameProjector = $this->getContainer()
            ->get(UserWithUsernameProjector::class)
        ;

        $signedUp1 = SampleEvents::signedUp();
        $signedUp2 = SampleEvents::anotherSignedUp();

        // EMPTY

        $userWithUsernameArray1 = $this->findUsersById(
            $signedUp1->aggregateId()->id()
        );
        $userWithUsernameArray2 = $this->findUsersById(
            $signedUp2->aggregateId()->id()
        );
        self::assertCount(
            0,
            $userWithUsernameArray1
        );
        self::assertCount(
            0,
            $userWithUsernameArray2
        );

        // SignedUp 1

        $UserWithUsernameProjector->project($signedUp1);
        $UserWithUsernameProjector->project($signedUp1); // test idempotency

        $userWithUsernameArray1 = $this->findUsersById(
            $signedUp1->aggregateId()->id()
        );
        $userWithUsernameArray2 = $this->findUsersById(
            $signedUp2->aggregateId()->id()
        );
        self::assertCount(
            1,
            $userWithUsernameArray1
        );
        self::assertSame(
            $signedUp1->aggregateId()->id(),
            $userWithUsernameArray1[0]->getUserId()
        );
        self::assertSame(
            strtolower($signedUp1->username()),
            $userWithUsernameArray1[0]->getCanonicalUsername()
        );
        self::assertCount(
            0,
            $userWithUsernameArray2
        );

        // SignedUp 2

        $UserWithUsernameProjector->project($signedUp2);
        $UserWithUsernameProjector->project($signedUp2); // test idempotency

        $userWithUsernameArray1 = $this->findUsersById(
            $signedUp1->aggregateId()->id()
        );
        $userWithUsernameArray2 = $this->findUsersById(
            $signedUp2->aggregateId()->id()
        );
        self::assertCount(
            1,
            $userWithUsernameArray1
        );
        self::assertSame(
            $signedUp1->aggregateId()->id(),
            $userWithUsernameArray1[0]->getUserId()
        );
        self::assertSame(
            strtolower($signedUp1->username()),
            $userWithUsernameArray1[0]->getCanonicalUsername()
        );
        self::assertCount(
            1,
            $userWithUsernameArray2
        );
        self::assertSame(
            $signedUp2->aggregateId()->id(),
            $userWithUsernameArray2[0]->getUserId()
        );
        self::assertSame(
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
