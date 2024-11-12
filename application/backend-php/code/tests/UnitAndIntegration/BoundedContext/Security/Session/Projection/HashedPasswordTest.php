<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Security\Session\Projection\HashedPassword;

use Galeas\Api\BoundedContext\Security\Session\Projection\HashedPassword\HashedPasswordFromUserId;
use Galeas\Api\BoundedContext\Security\Session\Projection\HashedPassword\HashedPasswordProjector;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\ResetsEventStoreAndProjectionsIntegrationTest;
use Tests\Galeas\Api\UnitAndIntegration\Util\SampleEvents;

class HashedPasswordTest extends ResetsEventStoreAndProjectionsIntegrationTest
{
    public function testEvents(): void
    {
        $hashedPasswordProjector = $this->getContainer()
            ->get(HashedPasswordProjector::class)
        ;

        $hashedPasswordFromUserId = $this->getContainer()
            ->get(HashedPasswordFromUserId::class)
        ;

        $signedUp = SampleEvents::signedUp();
        $hashedPasswordProjector->project($signedUp);

        Assert::assertEquals(
            $signedUp->hashedPassword(),
            $hashedPasswordFromUserId->hashedPasswordFromUserId($signedUp->aggregateId()->id())
        );
    }
}
