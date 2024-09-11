<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Security\Session\Projection\UserWithEmail;

use Galeas\Api\BoundedContext\Identity\User\Event\PrimaryEmailChangeRequested;
use Galeas\Api\BoundedContext\Identity\User\Event\PrimaryEmailVerified;
use Galeas\Api\BoundedContext\Identity\User\Event\SignedUp;
use Galeas\Api\BoundedContext\Security\Session\Projection\UserWithEmail\RequestedChange;
use Galeas\Api\BoundedContext\Security\Session\Projection\UserWithEmail\Unverified;
use Galeas\Api\BoundedContext\Security\Session\Projection\UserWithEmail\UserWithEmail;
use Galeas\Api\BoundedContext\Security\Session\Projection\UserWithEmail\UserWithEmailProcessor;
use Galeas\Api\BoundedContext\Security\Session\Projection\UserWithEmail\Verified;
use Galeas\Api\Common\Id\Id;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\KernelTestBase;

class UserWithEmailProcessorTest extends KernelTestBase
{
    /**
     * @test
     *
     * @throws \Exception
     */
    public function testProcessSignedUp(): void
    {
        $userWithEmailProcessorService = $this->getContainer()
            ->get(UserWithEmailProcessor::class);

        $signedUp = SignedUp::fromProperties(
            [],
            'tEst1@example.com',
            'password_test_123',
            'username_test',
            false
        );
        $userId = $signedUp->aggregateId()->id();
        $userWithEmailProcessorService->process($signedUp);

        Assert::assertEquals(
            [
                UserWithEmail::fromUserIdAndEmails(
                    $userId,
                    null,
                    'test1@example.com',
                    Unverified::setStatus()
                ),
            ],
            $this->findUserWithEmails($userId)
        );
    }

    /**
     * @test
     *
     * @throws \Exception
     */
    public function testProcessPrimaryEmailVerifiedWhenUnverified(): void
    {
        $userWithEmailProcessorService = $this->getContainer()
            ->get(UserWithEmailProcessor::class);

        $userId = Id::createNew();
        $this->getProjectionDocumentManager()
            ->persist(
                UserWithEmail::fromUserIdAndEmails(
                    $userId->id(),
                    null,
                    'unverified@galeas.com',
                    Unverified::setStatus()
                )
            );
        $this->getProjectionDocumentManager()->flush();

        $primaryEmailVerified = PrimaryEmailVerified::fromProperties(
            $userId,
            $userId,
            [],
            'fake_code'
        );
        $userWithEmailProcessorService->process($primaryEmailVerified);

        Assert::assertEquals(
            [
                UserWithEmail::fromUserIdAndEmails(
                    $userId->id(),
                    'unverified@galeas.com',
                    null,
                    Verified::setStatus()
                ),
            ],
            $this->findUserWithEmails($userId->id())
        );
    }

    /**
     * @test
     *
     * @throws \Exception
     */
    public function testProcessPrimaryEmailVerifiedWhenChangeRequested(): void
    {
        $userWithEmailProcessorService = $this->getContainer()
            ->get(UserWithEmailProcessor::class);

        $userId = Id::createNew();
        $this->getProjectionDocumentManager()
            ->persist(
                UserWithEmail::fromUserIdAndEmails(
                    $userId->id(),
                    'verified@galeas.com',
                    'requested@galeas.com',
                    RequestedChange::setStatus()
                )
            );
        $this->getProjectionDocumentManager()->flush();

        $primaryEmailVerified = PrimaryEmailVerified::fromProperties(
            $userId,
            $userId,
            [],
            'fake_code'
        );
        $userWithEmailProcessorService->process($primaryEmailVerified);

        Assert::assertEquals(
            [
                UserWithEmail::fromUserIdAndEmails(
                    $userId->id(),
                    'requested@galeas.com',
                    null,
                    Verified::setStatus()
                ),
            ],
            $this->findUserWithEmails($userId->id())
        );
    }

    /**
     * @test
     *
     * @throws \Exception
     */
    public function testProcessChangeRequestedWhenUnverified(): void
    {
        $userWithEmailProcessorService = $this->getContainer()
            ->get(UserWithEmailProcessor::class);

        $userId = Id::createNew();
        $this->getProjectionDocumentManager()
            ->persist(
                UserWithEmail::fromUserIdAndEmails(
                    $userId->id(),
                    null,
                    'unverified@galeas.com',
                    Unverified::setStatus()
                )
            );
        $this->getProjectionDocumentManager()->flush();

        $primaryEmailChangeRequested = PrimaryEmailChangeRequested::fromProperties(
            $userId,
            $userId,
            [],
            'new@galeas.com',
            'fake_hashed_password'
        );
        $userWithEmailProcessorService->process($primaryEmailChangeRequested);

        Assert::assertEquals(
            [
                UserWithEmail::fromUserIdAndEmails(
                    $userId->id(),
                    null,
                    'new@galeas.com',
                    Unverified::setStatus()
                ),
            ],
            $this->findUserWithEmails($userId->id())
        );
    }

    /**
     * @test
     *
     * @throws \Exception
     */
    public function testProcessChangeRequestedWhenVerified(): void
    {
        $userWithEmailProcessorService = $this->getContainer()
            ->get(UserWithEmailProcessor::class);

        $userId = Id::createNew();
        $this->getProjectionDocumentManager()
            ->persist(
                UserWithEmail::fromUserIdAndEmails(
                    $userId->id(),
                    'verified@galeas.com',
                    null,
                    Verified::setStatus()
                )
            );
        $this->getProjectionDocumentManager()->flush();

        $primaryEmailChangeRequested = PrimaryEmailChangeRequested::fromProperties(
            $userId,
            $userId,
            [],
            'new_requested@galeas.com',
            'fake_hashed_password'
        );
        $userWithEmailProcessorService->process($primaryEmailChangeRequested);

        Assert::assertEquals(
            [
                UserWithEmail::fromUserIdAndEmails(
                    $userId->id(),
                    'verified@galeas.com',
                    'new_requested@galeas.com',
                    RequestedChange::setStatus()
                ),
            ],
            $this->findUserWithEmails($userId->id())
        );
    }

    /**
     * @test
     *
     * @throws \Exception
     */
    public function testProcessChangeRequestedWhenChangeRequested(): void
    {
        $userWithEmailProcessorService = $this->getContainer()
            ->get(UserWithEmailProcessor::class);

        $userId = Id::createNew();
        $this->getProjectionDocumentManager()
            ->persist(
                UserWithEmail::fromUserIdAndEmails(
                    $userId->id(),
                    'verified@galeas.com',
                    'requested@galeas.com',
                    RequestedChange::setStatus()
                )
            );
        $this->getProjectionDocumentManager()->flush();

        $primaryEmailChangeRequested = PrimaryEmailChangeRequested::fromProperties(
            $userId,
            $userId,
            [],
            'new_requested@galeas.com',
            'fake_hashed_password'
        );
        $userWithEmailProcessorService->process($primaryEmailChangeRequested);

        Assert::assertEquals(
            [
                UserWithEmail::fromUserIdAndEmails(
                    $userId->id(),
                    'verified@galeas.com',
                    'new_requested@galeas.com',
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
