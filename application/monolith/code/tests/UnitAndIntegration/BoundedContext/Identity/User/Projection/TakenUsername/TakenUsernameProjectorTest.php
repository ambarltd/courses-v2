<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Identity\User\Projection\TakenUsername;

use Galeas\Api\BoundedContext\Identity\User\Event\SignedUp;
use Galeas\Api\BoundedContext\Identity\User\Projection\TakenUsername\TakenUsername;
use Galeas\Api\BoundedContext\Identity\User\Projection\TakenUsername\TakenUsernameProjector;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\KernelTestBase;
use Tests\Galeas\Api\UnitAndIntegration\Util\SampleEvents;

class TakenUsernameProjectorTest extends KernelTestBase
{
    public function testProcessSignedUpWithTwoUsers(): void
    {
        $TakenUsernameProjectorService = $this->getContainer()
            ->get(TakenUsernameProjector::class);

        $signedUp1 = SampleEvents::signedUp();
        $signedUp2 = SampleEvents::anotherSignedUp();

        $userId1 = $signedUp1->aggregateId()->id();
        $userId2 = $signedUp2->aggregateId()->id();
        $TakenUsernameProjectorService->project($signedUp1);
        $TakenUsernameProjectorService->project($signedUp2);

        $takenUsernames = $this->findTakenUsernames($userId1);
        Assert::assertEquals(
            [
                TakenUsername::fromUserIdAndUsername(
                    $userId1,
                    $signedUp1->username()
                ),
            ],
            $takenUsernames
        );
        $takenUsernames = $this->findTakenUsernames($userId2);
        Assert::assertEquals(
            [
                TakenUsername::fromUserIdAndUsername(
                    $userId2,
                    $signedUp2->username()
                ),
            ],
            $takenUsernames
        );
    }

    private function findTakenUsernames(string $userId): array
    {
        $queryBuilder = $this->getProjectionDocumentManager()
            ->createQueryBuilder(TakenUsername::class);

        $queryBuilder->field('id')->equals($userId);

        return array_values(
            $queryBuilder
                ->getQuery()
                ->execute()
                ->toArray()
        );
    }
}
