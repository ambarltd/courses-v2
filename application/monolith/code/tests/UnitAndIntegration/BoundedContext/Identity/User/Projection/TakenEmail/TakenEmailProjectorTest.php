<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Identity\User\Projection\TakenEmail;

use Galeas\Api\BoundedContext\Identity\User\Event\PrimaryEmailChangeRequested;
use Galeas\Api\BoundedContext\Identity\User\Event\PrimaryEmailVerified;
use Galeas\Api\BoundedContext\Identity\User\Event\SignedUp;
use Galeas\Api\BoundedContext\Identity\User\Projection\TakenEmail\TakenEmail;
use Galeas\Api\BoundedContext\Identity\User\Projection\TakenEmail\TakenEmailProjector;
use Galeas\Api\Common\Id\Id;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\KernelTestBase;

class TakenEmailProjectorTest extends KernelTestBase
{
    public function testProcessSignedUp(): void
    {
        $TakenEmailProjectorService = $this->getContainer()
            ->get(TakenEmailProjector::class);

        $signedUp = SignedUp::fromPropertiesAndDefaultOthers(
            [],
            'tEst1@example.com',
            'password_test_123',
            'username_test',
            false
        );
        $userId = $signedUp->aggregateId()->id();
        $TakenEmailProjectorService->process($signedUp);

        Assert::assertEquals(
            [
                TakenEmail::fromUserIdAndEmails(
                    $userId,
                    null,
                    'test1@example.com'
                ),
            ],
            $this->findTakenEmails($userId)
        );
    }

    public function testProcessSignedUpForTwoUsers(): void
    {
        $TakenEmailProjectorService = $this->getContainer()
            ->get(TakenEmailProjector::class);

        $signedUp1 = SignedUp::fromPropertiesAndDefaultOthers(
            [],
            'tEst1@example.com',
            'password_test_123',
            'username_test_1',
            false
        );
        $signedUp2 = SignedUp::fromPropertiesAndDefaultOthers(
            [],
            'Test2@example.com',
            'password_test_123',
            'username_test_2',
            false
        );
        $userId1 = $signedUp1->aggregateId()->id();
        $userId2 = $signedUp2->aggregateId()->id();
        $TakenEmailProjectorService->process($signedUp1);
        $TakenEmailProjectorService->process($signedUp2);

        Assert::assertEquals(
            [
                TakenEmail::fromUserIdAndEmails(
                    $userId1,
                    null,
                    'test1@example.com'
                ),
            ],
            $this->findTakenEmails($userId1)
        );
        Assert::assertEquals(
            [
                TakenEmail::fromUserIdAndEmails(
                    $userId2,
                    null,
                    'test2@example.com'
                ),
            ],
            $this->findTakenEmails($userId2)
        );
    }

    public function testProcessPrimaryEmailVerified(): void
    {
        $TakenEmailProjectorService = $this->getContainer()
            ->get(TakenEmailProjector::class);

        $takenEmail = TakenEmail::fromUserIdAndEmails(
            Id::createNew()->id(),
            'verified_email@example.com',
            'requested_email@example.com'
        );
        $this->getProjectionDocumentManager()->persist($takenEmail);
        $this->getProjectionDocumentManager()->flush();

        $signedUp = PrimaryEmailVerified::new(
            Id::fromId($takenEmail->getUserId()),
            Id::fromId($takenEmail->getUserId()),
            [],
            'code'
        );
        $userId = $signedUp->aggregateId()->id();
        $TakenEmailProjectorService->process($signedUp);

        Assert::assertEquals(
            [
                TakenEmail::fromUserIdAndEmails(
                    $userId,
                    'requested_email@example.com',
                    null
                ),
            ],
            $this->findTakenEmails($userId)
        );
    }

    public function testProcessPrimaryEmailChangeRequested(): void
    {
        $TakenEmailProjectorService = $this->getContainer()
            ->get(TakenEmailProjector::class);

        $takenEmail = TakenEmail::fromUserIdAndEmails(
            Id::createNew()->id(),
            'verified_email@example.com',
            'requested_email@example.com'
        );
        $this->getProjectionDocumentManager()->persist($takenEmail);
        $this->getProjectionDocumentManager()->flush();

        $signedUp = PrimaryEmailChangeRequested::fromProperties(
            Id::fromId($takenEmail->getUserId()),
            Id::fromId($takenEmail->getUserId()),
            [],
            'requested_new_email@example.com',
            'fake_hashed_password'
        );
        $userId = $signedUp->aggregateId()->id();
        $TakenEmailProjectorService->process($signedUp);

        Assert::assertEquals(
            [
                TakenEmail::fromUserIdAndEmails(
                    $userId,
                    'verified_email@example.com',
                    'requested_new_email@example.com'
                ),
            ],
            $this->findTakenEmails($userId)
        );
    }

    /**
     * @return TakenEmail[]
     *
     * @throws \Exception
     */
    private function findTakenEmails(string $userId): array
    {
        $queryBuilder = $this->getProjectionDocumentManager()
            ->createQueryBuilder(TakenEmail::class);

        $queryBuilder->field('id')->equals($userId);

        return array_values(
            $queryBuilder
                ->getQuery()
                ->execute()
                ->toArray()
        );
    }
}
