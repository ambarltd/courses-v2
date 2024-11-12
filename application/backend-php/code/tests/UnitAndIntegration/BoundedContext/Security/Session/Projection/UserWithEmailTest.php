<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Security\Session\Projection;

use Galeas\Api\BoundedContext\Security\Session\Projection\UserWithEmail\UserIdFromSignInEmail;
use Galeas\Api\BoundedContext\Security\Session\Projection\UserWithEmail\UserWithEmailProjector;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\ResetsEventStoreAndProjectionsIntegrationTest;
use Tests\Galeas\Api\UnitAndIntegration\Util\SampleEvents;

class UserWithEmailTest extends ResetsEventStoreAndProjectionsIntegrationTest
{
    public function testEvents(): void
    {
        $userWithEmailProjector = $this->getContainer()
            ->get(UserWithEmailProjector::class)
        ;
        $userIdFromSignInEmail = $this->getContainer()
            ->get(UserIdFromSignInEmail::class)
        ;

        $signedUp = SampleEvents::signedUp();
        $userWithEmailProjector->project($signedUp);
        Assert::assertEquals(
            null,
            $userIdFromSignInEmail->userIdFromSignInEmail($signedUp->primaryEmail())
        );

        $primaryEmailVerified = SampleEvents::primaryEmailVerified(
            $signedUp->aggregateId(),
            2,
            $signedUp->eventId(),
            $signedUp->eventId()
        );
        $userWithEmailProjector->project($primaryEmailVerified);
        Assert::assertEquals(
            $signedUp->aggregateId()->id(),
            $userIdFromSignInEmail->userIdFromSignInEmail($signedUp->primaryEmail())
        );

        $primaryEmailChangeRequested = SampleEvents::primaryEmailChangeRequested(
            $signedUp->aggregateId(),
            3,
            $signedUp->eventId(),
            $signedUp->eventId(),
        );
        $userWithEmailProjector->project($primaryEmailChangeRequested);
        Assert::assertEquals(
            $signedUp->aggregateId()->id(),
            $userIdFromSignInEmail->userIdFromSignInEmail($signedUp->primaryEmail())
        );

        $primaryEmailVerified = SampleEvents::primaryEmailVerified(
            $signedUp->aggregateId(),
            4,
            $signedUp->eventId(),
            $signedUp->eventId()
        );
        $userWithEmailProjector->project($primaryEmailVerified);
        Assert::assertEquals(
            $signedUp->aggregateId()->id(),
            $userIdFromSignInEmail->userIdFromSignInEmail($primaryEmailChangeRequested->newEmailRequested())
        );
    }
}
