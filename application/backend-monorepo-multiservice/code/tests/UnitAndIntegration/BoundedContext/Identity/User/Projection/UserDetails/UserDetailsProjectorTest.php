<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Identity\User\Projection\UserDetails;

use Galeas\Api\BoundedContext\Identity\User\Projection\UserDetails\UserDetails;
use Galeas\Api\BoundedContext\Identity\User\Projection\UserDetails\UserDetailsProjector;
use Galeas\Api\BoundedContext\Identity\User\Projection\UserDetails\ValueObject\UnverifiedEmail;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\ProjectionAndReactionIntegrationTest;
use Tests\Galeas\Api\UnitAndIntegration\Util\SampleEvents;

class UserDetailsProjectorTest extends ProjectionAndReactionIntegrationTest
{
    public function testProcessSignedUp(): void
    {
        $UserDetailsProjectorService = $this->getContainer()
            ->get(UserDetailsProjector::class)
        ;

        $signedUp = SampleEvents::signedUp();
        $userId = $signedUp->aggregateId()->id();
        $UserDetailsProjectorService->project($signedUp);

        Assert::assertEquals(
            UserDetails::fromProperties(
                $userId,
                UnverifiedEmail::fromProperties($signedUp->primaryEmail())
            ),
            $this->findUserDetails($userId)
        );
    }

    /**
     * @throws \Exception
     */
    private function findUserDetails(string $userId): ?UserDetails
    {
        $queryBuilder = $this->getProjectionDocumentManager()
            ->createQueryBuilder(UserDetails::class)
        ;

        $queryBuilder->field('id')->equals($userId);

        $userDetails = $queryBuilder
            ->getQuery()
            ->getSingleResult()
        ;

        if ($userDetails instanceof UserDetails) {
            return $userDetails;
        }

        if (null === $userDetails) {
            return null;
        }

        throw new \Exception('Unexpected type');
    }
}
