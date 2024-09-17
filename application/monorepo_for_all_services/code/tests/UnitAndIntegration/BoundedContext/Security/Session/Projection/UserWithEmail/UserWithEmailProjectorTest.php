<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Security\Session\Projection\UserWithEmail;

use Galeas\Api\BoundedContext\Security\Session\Projection\UserWithEmail\RequestedChange;
use Galeas\Api\BoundedContext\Security\Session\Projection\UserWithEmail\Unverified;
use Galeas\Api\BoundedContext\Security\Session\Projection\UserWithEmail\UserWithEmail;
use Galeas\Api\BoundedContext\Security\Session\Projection\UserWithEmail\UserWithEmailProjector;
use Galeas\Api\BoundedContext\Security\Session\Projection\UserWithEmail\Verified;
use Galeas\Api\Common\Id\Id;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\KernelTestBase;
use Tests\Galeas\Api\UnitAndIntegration\Util\SampleEvents;

class UserWithEmailProjectorTest extends KernelTestBase
{
    public function testProcessSignedUp(): void
    {
        $UserWithEmailProjectorService = $this->getContainer()
            ->get(UserWithEmailProjector::class);

        $signedUp = SampleEvents::signedUp();
        $userId = $signedUp->aggregateId()->id();
        $UserWithEmailProjectorService->project($signedUp);

        Assert::assertEquals(
            [
                UserWithEmail::fromUserIdAndEmails(
                    $userId,
                    null,
                    $signedUp->primaryEmail(),
                    Unverified::setStatus()
                ),
            ],
            $this->findUserWithEmails($userId)
        );
    }

    public function testProcessPrimaryEmailVerifiedWhenUnverified(): void
    {
        $UserWithEmailProjectorService = $this->getContainer()
            ->get(UserWithEmailProjector::class);

        $userId = Id::createNew();
        $this->getProjectionDocumentManager()
            ->persist(
                UserWithEmail::fromUserIdAndEmails(
                    $userId->id(),
                    null,
                    'unverified189181727@galeas.com',
                    Unverified::setStatus()
                )
            );
        $this->getProjectionDocumentManager()->flush();

        $primaryEmailVerified = SampleEvents::primaryEmailVerified(
            $userId,
            2,
            Id::createNew(),
            Id::createNew()
        );
        $UserWithEmailProjectorService->project($primaryEmailVerified);

        Assert::assertEquals(
            [
                UserWithEmail::fromUserIdAndEmails(
                    $userId->id(),
                    'unverified189181727@galeas.com',
                    null,
                    Verified::setStatus()
                ),
            ],
            $this->findUserWithEmails($userId->id())
        );
    }

    public function testProcessPrimaryEmailVerifiedWhenChangeRequested(): void
    {
        $UserWithEmailProjectorService = $this->getContainer()
            ->get(UserWithEmailProjector::class);

        $userId = Id::createNew();
        $this->getProjectionDocumentManager()
            ->persist(
                UserWithEmail::fromUserIdAndEmails(
                    $userId->id(),
                    'verified189181727@galeas.com',
                    'requested189181727@galeas.com',
                    RequestedChange::setStatus()
                )
            );
        $this->getProjectionDocumentManager()->flush();

        $primaryEmailVerified = SampleEvents::primaryEmailVerified(
            $userId,
            2,
            Id::createNew(),
            Id::createNew()
        );
        $UserWithEmailProjectorService->project($primaryEmailVerified);

        Assert::assertEquals(
            [
                UserWithEmail::fromUserIdAndEmails(
                    $userId->id(),
                    'requested189181727@galeas.com',
                    null,
                    Verified::setStatus()
                ),
            ],
            $this->findUserWithEmails($userId->id())
        );
    }

    public function testProcessChangeRequestedWhenUnverified(): void
    {
        $UserWithEmailProjectorService = $this->getContainer()
            ->get(UserWithEmailProjector::class);

        $userId = Id::createNew();
        $this->getProjectionDocumentManager()
            ->persist(
                UserWithEmail::fromUserIdAndEmails(
                    $userId->id(),
                    null,
                    'unverified189181727@galeas.com',
                    Unverified::setStatus()
                )
            );
        $this->getProjectionDocumentManager()->flush();

        $primaryEmailChangeRequested = SampleEvents::primaryEmailChangeRequested(
            $userId,
            33,
            Id::createNew(),
            Id::createNew(),
        );
        $UserWithEmailProjectorService->project($primaryEmailChangeRequested);

        Assert::assertEquals(
            [
                UserWithEmail::fromUserIdAndEmails(
                    $userId->id(),
                    null,
                    $primaryEmailChangeRequested->newEmailRequested(),
                    Unverified::setStatus()
                ),
            ],
            $this->findUserWithEmails($userId->id())
        );
    }

    public function testProcessChangeRequestedWhenVerified(): void
    {
        $UserWithEmailProjectorService = $this->getContainer()
            ->get(UserWithEmailProjector::class);

        $userId = Id::createNew();
        $this->getProjectionDocumentManager()
            ->persist(
                UserWithEmail::fromUserIdAndEmails(
                    $userId->id(),
                    'verified189181727@galeas.com',
                    null,
                    Verified::setStatus()
                )
            );
        $this->getProjectionDocumentManager()->flush();

        $primaryEmailChangeRequested = SampleEvents::primaryEmailChangeRequested(
            $userId,
            43,
            Id::createNew(),
            Id::createNew()
        );
        $UserWithEmailProjectorService->project($primaryEmailChangeRequested);

        Assert::assertEquals(
            [
                UserWithEmail::fromUserIdAndEmails(
                    $userId->id(),
                    'verified189181727@galeas.com',
                    $primaryEmailChangeRequested->newEmailRequested(),
                    RequestedChange::setStatus()
                ),
            ],
            $this->findUserWithEmails($userId->id())
        );
    }

    public function testProcessChangeRequestedWhenChangeRequested(): void
    {
        $UserWithEmailProjectorService = $this->getContainer()
            ->get(UserWithEmailProjector::class);

        $userId = Id::createNew();
        $this->getProjectionDocumentManager()
            ->persist(
                UserWithEmail::fromUserIdAndEmails(
                    $userId->id(),
                    'verified189181727@galeas.com',
                    'requested189181727@galeas.com',
                    RequestedChange::setStatus()
                )
            );
        $this->getProjectionDocumentManager()->flush();

        $primaryEmailChangeRequested = SampleEvents::primaryEmailChangeRequested(
            $userId,
            182,
            Id::createNew(),
            Id::createNew(),
        );
        $UserWithEmailProjectorService->project($primaryEmailChangeRequested);

        Assert::assertEquals(
            [
                UserWithEmail::fromUserIdAndEmails(
                    $userId->id(),
                    'verified189181727@galeas.com',
                    $primaryEmailChangeRequested->newEmailRequested(),
                    RequestedChange::setStatus()
                ),
            ],
            $this->findUserWithEmails($userId->id())
        );
    }

    /**
     * @return UserWithEmail[]
     *
     * @throws \Exception
     */
    private function findUserWithEmails(string $userId): array
    {
        $queryBuilder = $this->getProjectionDocumentManager()
            ->createQueryBuilder(UserWithEmail::class);

        $queryBuilder->field('id')->equals($userId);

        return array_values(
            $queryBuilder
                ->getQuery()
                ->execute()
                ->toArray()
        );
    }
}
