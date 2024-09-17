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
use Tests\Galeas\Api\UnitAndIntegration\Util\SampleEvents;

class PrimaryEmailVerificationCodeProjectorTest extends KernelTestBase
{
    public function testProcessSignedUp(): void
    {
        $processorService = $this->getContainer()
            ->get(PrimaryEmailVerificationCodeProjector::class);

        $signedUp = SampleEvents::signedUp();
        $userId = $signedUp->aggregateId()->id();
        $primaryEmailVerificationCode = $signedUp->primaryEmailVerificationCode();
        $processorService->project($signedUp);

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

        $primaryEmailChangeRequested = SampleEvents::primaryEmailChangeRequested(
            Id::createNew(),
            2,
            Id::createNew(),
            Id::createNew(),
        );
        $userId = $primaryEmailChangeRequested->aggregateId()->id();
        $primaryEmailVerificationCode = $primaryEmailChangeRequested->newVerificationCode();
        $processorService->project($primaryEmailChangeRequested);

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

        $signedUp = SampleEvents::signedUp();
        $primaryEmailVerified = SampleEvents::primaryEmailVerified(
            $signedUp->aggregateId(),
            2,
            $signedUp->eventId(),
            $signedUp->eventId(),
        );
        $userId = $primaryEmailVerified->aggregateId()->id();
        $processorService->project($signedUp);
        $processorService->project($primaryEmailVerified);

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
