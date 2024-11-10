<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Identity\User\Projection\UserDetailsV2;

use Galeas\Api\BoundedContext\Identity\User\Projection\UserDetailsV2\UserDetails;
use Galeas\Api\BoundedContext\Identity\User\Projection\UserDetailsV2\UserDetailsProjector;
use Galeas\Api\BoundedContext\Identity\User\Projection\UserDetailsV2\ValueObject\UnverifiedEmail;
use Galeas\Api\BoundedContext\Identity\User\Projection\UserDetailsV2\ValueObject\VerifiedEmail;
use Galeas\Api\BoundedContext\Identity\User\Projection\UserDetailsV2\ValueObject\VerifiedEmailButRequestedNewEmail;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\ResetsEventStoreAndProjectionsIntegrationTest;
use Tests\Galeas\Api\UnitAndIntegration\Util\SampleEvents;

class UserDetailsProjectorTest extends ResetsEventStoreAndProjectionsIntegrationTest
{
    public function testProcess(): void
    {
        $projector = $this->getContainer()
            ->get(UserDetailsProjector::class)
        ;

        $signedUp = SampleEvents::signedUp();
        $userId = $signedUp->aggregateId()->id();
        $projector->project($signedUp);
        $projector->project($signedUp); // testing idempotence is fine
        Assert::assertEquals(
            UserDetails::fromProperties(
                $userId,
                UnverifiedEmail::fromProperties($signedUp->primaryEmail())
            ),
            $this->findUserDetails($userId)
        );

        $requestedNew = SampleEvents::primaryEmailChangeRequested(
            $signedUp->aggregateId(),
            $signedUp->aggregateVersion() + 1,
            $signedUp->eventId(),
            $signedUp->eventId()
        );
        $projector->project($requestedNew);
        Assert::assertEquals(
            UserDetails::fromProperties(
                $userId,
                UnverifiedEmail::fromProperties($requestedNew->newEmailRequested())
            ),
            $this->findUserDetails($userId)
        );

        $verified = SampleEvents::primaryEmailVerified(
            $signedUp->aggregateId(),
            $requestedNew->aggregateVersion() + 2,
            $signedUp->eventId(),
            $requestedNew->eventId()
        );
        $projector->project($verified);
        Assert::assertEquals(
            UserDetails::fromProperties(
                $userId,
                VerifiedEmail::fromProperties($requestedNew->newEmailRequested())
            ),
            $this->findUserDetails($userId)
        );

        $requestedNewAgain = SampleEvents::primaryEmailChangeRequestedAgain(
            $signedUp->aggregateId(),
            $verified->aggregateVersion() + 1,
            $signedUp->eventId(),
            $verified->eventId()
        );
        $projector->project($requestedNewAgain);
        Assert::assertEquals(
            UserDetails::fromProperties(
                $userId,
                VerifiedEmailButRequestedNewEmail::fromProperties($requestedNew->newEmailRequested(), $requestedNewAgain->newEmailRequested())
            ),
            $this->findUserDetails($userId)
        );

        $requestedNewAgainAgain = SampleEvents::primaryEmailChangeRequestedAgainAgain(
            $signedUp->aggregateId(),
            $requestedNewAgain->aggregateVersion() + 1,
            $signedUp->eventId(),
            $requestedNewAgain->eventId()
        );
        $projector->project($requestedNewAgainAgain);
        Assert::assertEquals(
            UserDetails::fromProperties(
                $userId,
                VerifiedEmailButRequestedNewEmail::fromProperties($requestedNew->newEmailRequested(), $requestedNewAgainAgain->newEmailRequested())
            ),
            $this->findUserDetails($userId)
        );

        $primaryEmailVerifiedAgain = SampleEvents::primaryEmailVerified(
            $signedUp->aggregateId(),
            $requestedNewAgainAgain->aggregateVersion() + 1,
            $signedUp->eventId(),
            $requestedNewAgainAgain->eventId()
        );
        $projector->project($primaryEmailVerifiedAgain);
        Assert::assertEquals(
            UserDetails::fromProperties(
                $userId,
                VerifiedEmail::fromProperties($requestedNewAgainAgain->newEmailRequested())
            ),
            $this->findUserDetails($userId)
        );
    }

    /**
     * @throws \Exception
     */
    private function findUserDetails(string $userId): ?UserDetails
    {
        $this->getProjectionDocumentManager()->clear();
        $queryBuilder = $this->getProjectionDocumentManager()
            ->createQueryBuilder(UserDetails::class)
        ;

        $queryBuilder->field('id')->equals($userId);

        $userDetails = $queryBuilder
            ->getQuery()
            ->getSingleResult()
        ;

        $this->getProjectionDocumentManager()->clear();
        if ($userDetails instanceof UserDetails) {
            return $userDetails;
        }

        if (null === $userDetails) {
            return null;
        }

        throw new \Exception('Unexpected type');
    }
}
