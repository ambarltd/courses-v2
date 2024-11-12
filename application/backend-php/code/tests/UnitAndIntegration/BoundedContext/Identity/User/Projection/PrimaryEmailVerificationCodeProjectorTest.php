<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Identity\User\Projection;

use Galeas\Api\BoundedContext\Identity\User\Projection\PrimaryEmailVerificationCode\PrimaryEmailVerificationCodeProjector;
use Galeas\Api\BoundedContext\Identity\User\Projection\PrimaryEmailVerificationCode\UserIdFromPrimaryEmailVerificationCode;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\ResetsEventStoreAndProjectionsIntegrationTest;
use Tests\Galeas\Api\UnitAndIntegration\Util\SampleEvents;

class PrimaryEmailVerificationCodeProjectorTest extends ResetsEventStoreAndProjectionsIntegrationTest
{
    public function testEvents()
    {
        $primaryEmailVerificationCodeProjector = $this->getContainer()
            ->get(PrimaryEmailVerificationCodeProjector::class)
        ;
        $primaryEmailVerificationCode = $this->getContainer()
            ->get(UserIdFromPrimaryEmailVerificationCode::class);

        $signedUp = SampleEvents::signedUp();
        $primaryEmailVerificationCodeProjector->project($signedUp);
        Assert::assertEquals(
            $signedUp->aggregateId()->id(),
            $primaryEmailVerificationCode->userIdFromPrimaryEmailVerificationCode($signedUp->primaryEmailVerificationCode())
        );

        $primaryEmailVerified = SampleEvents::primaryEmailVerified(
            $signedUp->aggregateId(),
            2,
            $signedUp->eventId(),
            $signedUp->eventId()
        );
        $primaryEmailVerificationCodeProjector->project($primaryEmailVerified);
        Assert::assertEquals(
            null,
            $primaryEmailVerificationCode->userIdFromPrimaryEmailVerificationCode($signedUp->primaryEmailVerificationCode())
        );

        $primaryEmailChangeRequested = SampleEvents::primaryEmailChangeRequested(
            $signedUp->aggregateId(),
            3,
            $signedUp->eventId(),
            $signedUp->eventId(),
        );
        $primaryEmailVerificationCodeProjector->project($primaryEmailChangeRequested);
        Assert::assertEquals(
            $signedUp->aggregateId()->id(),
            $primaryEmailVerificationCode->userIdFromPrimaryEmailVerificationCode($primaryEmailChangeRequested->newVerificationCode())
        );

        $primaryEmailVerified = SampleEvents::primaryEmailVerified(
            $signedUp->aggregateId(),
            4,
            $signedUp->eventId(),
            $signedUp->eventId()
        );
        $primaryEmailVerificationCodeProjector->project($primaryEmailVerified);
        Assert::assertEquals(
            null,
            $primaryEmailVerificationCode->userIdFromPrimaryEmailVerificationCode($primaryEmailChangeRequested->newVerificationCode())
        );
    }

}