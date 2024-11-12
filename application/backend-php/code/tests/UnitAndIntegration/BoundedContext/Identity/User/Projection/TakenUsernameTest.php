<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Identity\User\Projection;

use Galeas\Api\BoundedContext\Identity\User\Projection\TakenUsername\IsUsernameTaken;
use Galeas\Api\BoundedContext\Identity\User\Projection\TakenUsername\TakenUsernameProjector;
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
            ->get(IsUsernameTaken::class)
        ;

        $signedUp = SampleEvents::signedUp();
        $takenUsernameProjector->project($signedUp);
        Assert::assertFalse(
            $isUsernameTaken->isUsernameTaken($signedUp->username())
        );

        $primaryEmailVerified = SampleEvents::primaryEmailVerified(
            $signedUp->aggregateId(),
            2,
            $signedUp->eventId(),
            $signedUp->eventId()
        );
        $takenUsernameProjector->project($primaryEmailVerified);
        Assert::assertTrue(
            $isUsernameTaken->isUsernameTaken($signedUp->username())
        );
    }
}
