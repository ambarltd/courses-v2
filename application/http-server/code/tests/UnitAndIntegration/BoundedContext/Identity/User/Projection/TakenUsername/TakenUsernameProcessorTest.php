<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Identity\User\Projection\TakenUsername;

use Galeas\Api\BoundedContext\Identity\User\Event\SignedUp;
use Galeas\Api\BoundedContext\Identity\User\Projection\TakenUsername\TakenUsername;
use Galeas\Api\BoundedContext\Identity\User\Projection\TakenUsername\TakenUsernameProcessor;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\KernelTestBase;

class TakenUsernameProcessorTest extends KernelTestBase
{
    /**
     * @test
     *
     * @throws \Exception
     */
    public function testProcessSignedUpWithTwoUsers(): void
    {
        $takenUsernameProcessorService = $this->getContainer()
            ->get(TakenUsernameProcessor::class);

        $signedUp1 = SignedUp::fromProperties(
            [],
            'tEst1@example.com',
            'password_test_123',
            'uSername_test',
            false
        );
        $signedUp2 = SignedUp::fromProperties(
            [],
            'tEst1@example.com',
            'password_test_123',
            'username_tEst_2',
            false
        );

        $userId1 = $signedUp1->aggregateId()->id();
        $userId2 = $signedUp2->aggregateId()->id();
        $takenUsernameProcessorService->process($signedUp1);
        $takenUsernameProcessorService->process($signedUp2);

        $takenUsernames = $this->findTakenUsernames($userId1);
        Assert::assertEquals(
            [
                TakenUsername::fromUserIdAndUsername(
                    $userId1,
                    'username_test'
                ),
            ],
            $takenUsernames
        );
        $takenUsernames = $this->findTakenUsernames($userId2);
        Assert::assertEquals(
            [
                TakenUsername::fromUserIdAndUsername(
                    $userId2,
                    'username_test_2'
                ),
            ],
            $takenUsernames
        );
    }

    /**
     * @return TakenUsername[]
     *
     * @throws \Exception
     */
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
