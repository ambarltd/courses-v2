<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Identity\User\Projection;

use Galeas\Api\BoundedContext\Identity\User\Projection\UserDetails\GetUserDetails;
use Galeas\Api\BoundedContext\Identity\User\Projection\UserDetails\UserDetailsProjector;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\ResetsEventStoreAndProjectionsIntegrationTest;
use Tests\Galeas\Api\UnitAndIntegration\Util\SampleEvents;

class UserDetailsTest extends ResetsEventStoreAndProjectionsIntegrationTest
{
    public function testEvents(): void
    {
        $userDetailsProjector = $this->getContainer()
            ->get(UserDetailsProjector::class)
        ;
        $getUserDetails = $this->getContainer()
            ->get(GetUserDetails::class)
        ;

        $signedUp = SampleEvents::signedUp();
        $userDetailsProjector->projectIdempotently('test', $signedUp);
        Assert::assertEquals(
            [
                'userId' => $signedUp->aggregateId()->id(),
                'primaryEmailStatus' => [
                    'unverifiedEmail' => [
                        'email' => $signedUp->primaryEmail(),
                    ],
                ],
            ],
            $getUserDetails->getUserDetails($signedUp->aggregateId()->id())
        );

        $primaryEmailVerified = SampleEvents::primaryEmailVerified(
            $signedUp->aggregateId(),
            2,
            $signedUp->eventId(),
            $signedUp->eventId()
        );
        $userDetailsProjector->projectIdempotently('test', $primaryEmailVerified);
        Assert::assertEquals(
            [
                'userId' => $signedUp->aggregateId()->id(),
                'primaryEmailStatus' => [
                    'verifiedEmail' => [
                        'email' => $signedUp->primaryEmail(),
                    ],
                ],
            ],
            $getUserDetails->getUserDetails($signedUp->aggregateId()->id())
        );

        $primaryEmailChangeRequested = SampleEvents::primaryEmailChangeRequested(
            $signedUp->aggregateId(),
            3,
            $signedUp->eventId(),
            $signedUp->eventId(),
        );
        $userDetailsProjector->projectIdempotently('test', $primaryEmailChangeRequested);
        Assert::assertEquals(
            [
                'userId' => $signedUp->aggregateId()->id(),
                'primaryEmailStatus' => [
                    'verifiedButRequestedNewEmail' => [
                        'requestedEmail' => $primaryEmailChangeRequested->newEmailRequested(),
                        'verifiedEmail' => $signedUp->primaryEmail(),
                    ],
                ],
            ],
            $getUserDetails->getUserDetails($signedUp->aggregateId()->id())
        );

        $primaryEmailVerified = SampleEvents::primaryEmailVerified(
            $signedUp->aggregateId(),
            4,
            $signedUp->eventId(),
            $signedUp->eventId()
        );
        $userDetailsProjector->projectIdempotently('test', $primaryEmailVerified);
        Assert::assertEquals(
            [
                'userId' => $signedUp->aggregateId()->id(),
                'primaryEmailStatus' => [
                    'verifiedEmail' => [
                        'email' => $primaryEmailChangeRequested->newEmailRequested(),
                    ],
                ],
            ],
            $getUserDetails->getUserDetails($signedUp->aggregateId()->id())
        );
    }
}
