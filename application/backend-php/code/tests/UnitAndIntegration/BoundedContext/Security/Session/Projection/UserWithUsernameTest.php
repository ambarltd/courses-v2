<?php

declare(strict_types=1);

namespace Galeas\Api\Tests\UnitAndIntegration\BoundedContext\Security\Session\Projection\UserWithUsername;

use Galeas\Api\BoundedContext\Security\Session\Projection\UserWithUsername\UserIdFromSignInUsername;
use Galeas\Api\BoundedContext\Security\Session\Projection\UserWithUsername\UserWithUsernameProjector;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\ResetsEventStoreAndProjectionsIntegrationTest;
use Tests\Galeas\Api\UnitAndIntegration\Util\SampleEvents;

class UserWithUsernameTest extends ResetsEventStoreAndProjectionsIntegrationTest
{
    public function testEvents(): void
    {
        $userWithUsernameProjector = $this->getContainer()
            ->get(UserWithUsernameProjector::class)
        ;
        $userIdFromSignInUsername = $this->getContainer()
            ->get(UserIdFromSignInUsername::class)
        ;

        $signedUp = SampleEvents::signedUp();
        $userWithUsernameProjector->project($signedUp);
        Assert::assertEquals(
            null,
            $userIdFromSignInUsername->userIdFromSignInUsername($signedUp->username())
        );

        $primaryEmailVerified = SampleEvents::primaryEmailVerified(
            $signedUp->aggregateId(),
            2,
            $signedUp->eventId(),
            $signedUp->eventId()
        );
        $userWithUsernameProjector->project($primaryEmailVerified);
        Assert::assertEquals(
            $signedUp->aggregateId()->id(),
            $userIdFromSignInUsername->userIdFromSignInUsername($signedUp->username())
        );
    }
}
