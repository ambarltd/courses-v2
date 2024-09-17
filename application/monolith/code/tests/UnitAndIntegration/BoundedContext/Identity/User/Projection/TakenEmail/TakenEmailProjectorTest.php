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
use Tests\Galeas\Api\UnitAndIntegration\Util\SampleEvents;

class TakenEmailProjectorTest extends KernelTestBase
{
    public function testProcessSignedUp(): void
    {
        $TakenEmailProjectorService = $this->getContainer()
            ->get(TakenEmailProjector::class);

        $signedUp = SampleEvents::signedUp();
        $userId = $signedUp->aggregateId()->id();
        $TakenEmailProjectorService->project($signedUp);

        Assert::assertEquals(
            [
                TakenEmail::fromUserIdAndEmails(
                    $userId,
                    null,
                    $signedUp->primaryEmail()
                ),
            ],
            $this->findTakenEmails($userId)
        );
    }

    public function testProcessSignedUpForTwoUsers(): void
    {
        $TakenEmailProjectorService = $this->getContainer()
            ->get(TakenEmailProjector::class);

        $signedUp1 = SampleEvents::signedUp();
        $signedUp2 = SampleEvents::anotherSignedUp();
        $userId1 = $signedUp1->aggregateId()->id();
        $userId2 = $signedUp2->aggregateId()->id();
        $TakenEmailProjectorService->project($signedUp1);
        $TakenEmailProjectorService->project($signedUp2);

        Assert::assertEquals(
            [
                TakenEmail::fromUserIdAndEmails(
                    $userId1,
                    null,
                    $signedUp1->primaryEmail()
                ),
            ],
            $this->findTakenEmails($userId1)
        );
        Assert::assertEquals(
            [
                TakenEmail::fromUserIdAndEmails(
                    $userId2,
                    null,
                    $signedUp2->primaryEmail()
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

        $primaryEmailVerified = SampleEvents::primaryEmailVerified(
            Id::fromId($takenEmail->getUserId()),
            2,
            Id::createNew(),
            Id::createNew()
        );
        $userId = $primaryEmailVerified->aggregateId()->id();
        $TakenEmailProjectorService->project($primaryEmailVerified);

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

        $primaryEmailChangeRequested = SampleEvents::primaryEmailChangeRequested(
            Id::fromId($takenEmail->getUserId()),
            2,
            Id::createNew(),
            Id::createNew(),
        );
        $userId = $primaryEmailChangeRequested->aggregateId()->id();
        $TakenEmailProjectorService->project($primaryEmailChangeRequested);

        Assert::assertEquals(
            [
                TakenEmail::fromUserIdAndEmails(
                    $userId,
                    'verified_email@example.com',
                    $primaryEmailChangeRequested->newEmailRequested()
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
