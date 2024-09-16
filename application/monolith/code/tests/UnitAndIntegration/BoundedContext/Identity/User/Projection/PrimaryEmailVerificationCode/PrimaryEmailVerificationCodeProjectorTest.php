<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Identity\User\Projection\PrimaryEmailVerificationCode;

use Galeas\Api\BoundedContext\Identity\User\Event\PrimaryEmailChangeRequested;
use Galeas\Api\BoundedContext\Identity\User\Event\PrimaryEmailVerified;
use Galeas\Api\BoundedContext\Identity\User\Event\SignedUp;
use Galeas\Api\BoundedContext\Identity\User\Projection\PrimaryEmailVerificationCode\PrimaryEmailVerificationCode;
use Galeas\Api\BoundedContext\Identity\User\Projection\PrimaryEmailVerificationCode\PrimaryEmailVerificationCodeProjector;
use Galeas\Api\Common\Id\Id;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\KernelTestBase;

class PrimaryEmailVerificationCodeProjectorTest extends KernelTestBase
{
    public function testProcessSignedUp(): void
    {
        $processorService = $this->getContainer()
            ->get(PrimaryEmailVerificationCodeProjector::class);

        $signedUp = SignedUp::fromPropertiesAndDefaultOthers(
            [],
            'tEst1@example.com',
            'password_test_123',
            'uSername_test',
            false
        );
        $userId = $signedUp->aggregateId()->id();
        $primaryEmailVerificationCode = $signedUp->primaryEmailVerificationCode();
        $processorService->process($signedUp);

        Assert::assertEquals(
            [
                PrimaryEmailVerificationCode::fromUserIdAndVerificationCode(
                    $userId,
                    $primaryEmailVerificationCode
                ),
            ],
            $this->findUserIdToPrimaryEmailVerificationCode($userId)
        );
    }

    public function testProcessPrimaryEmailChangeRequested(): void
    {
        $processorService = $this->getContainer()
            ->get(PrimaryEmailVerificationCodeProjector::class);

        $primaryEmailChangeRequested = PrimaryEmailChangeRequested::fromProperties(
            Id::createNew(),
            Id::createNew(),
            [],
            'tEst1@example.com',
            'fake_hashed_password'
        );
        $userId = $primaryEmailChangeRequested->aggregateId()->id();
        $primaryEmailVerificationCode = $primaryEmailChangeRequested->newVerificationCode();
        $processorService->process($primaryEmailChangeRequested);

        Assert::assertEquals(
            [
                PrimaryEmailVerificationCode::fromUserIdAndVerificationCode(
                    $userId,
                    $primaryEmailVerificationCode
                ),
            ],
            $this->findUserIdToPrimaryEmailVerificationCode($userId)
        );
    }

    public function testProcessPrimaryEmailVerifiedAfterSignedUp(): void
    {
        $processorService = $this->getContainer()
            ->get(PrimaryEmailVerificationCodeProjector::class);

        $signedUp = SignedUp::fromPropertiesAndDefaultOthers(
            [],
            'tEst1@example.com',
            'password_test_123',
            'uSername_test',
            false
        );
        $primaryEmailVerified = PrimaryEmailVerified::new(
            $signedUp->aggregateId(),
            $signedUp->aggregateId(),
            [],
            "should_be_ignored",
        );
        $userId = $primaryEmailVerified->aggregateId()->id();
        $processorService->process($signedUp);
        $processorService->process($primaryEmailVerified);

        Assert::assertEquals(
            [
                PrimaryEmailVerificationCode::fromUserIdAndVerificationCode(
                    $userId,
                    null
                ),
            ],
            $this->findUserIdToPrimaryEmailVerificationCode($userId)
        );
    }

    /**
     * @return PrimaryEmailVerificationCode[]
     *
     * @throws \Exception
     */
    private function findUserIdToPrimaryEmailVerificationCode(string $userId): array
    {
        $queryBuilder = $this->getProjectionDocumentManager()
            ->createQueryBuilder(PrimaryEmailVerificationCode::class);

        $queryBuilder->field('id')->equals($userId);

        return array_values(
            $queryBuilder
                ->getQuery()
                ->execute()
                ->toArray()
        );
    }
}
