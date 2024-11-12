<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Identity\User\Projection;

use Galeas\Api\BoundedContext\Identity\User\Projection\TakenUsername\TakenUsernameProjector;
use Galeas\Api\BoundedContext\Identity\User\Projection\UserDetails\GetUserDetails;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\ResetsEventStoreAndProjectionsIntegrationTest;
use Tests\Galeas\Api\UnitAndIntegration\Util\SampleEvents;

class TakenUsernameTest extends ResetsEventStoreAndProjectionsIntegrationTest
{
    public function testEvents(): void
    {
        $takenUsernameProjector = $this->getContainer()
            ->get(TakenUsernameProjector::class)
        ;
        $isUsernameTaken = $this->getContainer()
            ->get(GetUserDetails::class)
        ;

        $signedUp = SampleEvents::signedUp();
        Assert::assertEquals(
            false,
            $isUsernameTaken->getUserDetails($signedUp->aggregateId()->id())
        );

        $takenUsernameProjector->project($signedUp);
        Assert::assertEquals(
            true,
            $isUsernameTaken->getUserDetails($signedUp->aggregateId()->id())
        );
    }
}
