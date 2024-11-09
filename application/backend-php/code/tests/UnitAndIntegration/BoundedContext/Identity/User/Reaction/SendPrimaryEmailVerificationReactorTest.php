<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Identity\User\Reaction;

use Galeas\Api\BoundedContext\Identity\User\Event\PrimaryEmailVerificationCodeSent;
use Galeas\Api\BoundedContext\Identity\User\Reaction\SendPrimaryEmailVerification\SendPrimaryEmailVerificationReactor;
use Galeas\Api\Common\Id\Id;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\ProjectionAndReactionIntegrationTest;
use Tests\Galeas\Api\UnitAndIntegration\Util\SampleEvents;

class SendPrimaryEmailVerificationReactorTest extends ProjectionAndReactionIntegrationTest
{
    public function testReactToSignedUp(): void
    {
        /** @var SendPrimaryEmailVerificationReactor $sendPrimaryEmailVerificationReactor */
        $sendPrimaryEmailVerificationReactor = $this->getContainer()
            ->get(SendPrimaryEmailVerificationReactor::class)
        ;

        $signedUp = SampleEvents::signedUp();
        $this->getSQLEventStore()->beginTransaction();
        $this->getSQLEventStore()->save($signedUp);
        $this->getSQLEventStore()->completeTransaction();
        $sendPrimaryEmailVerificationReactor->react($signedUp);

        $expectedPrimaryEmailVerificationCodeSent = PrimaryEmailVerificationCodeSent::new(
            Id::createNewByHashing('Identity/User/PrimaryEmailVerificationSent:'.$signedUp->eventId()->id()),
            $signedUp->aggregateId(),
            $signedUp->aggregateVersion() + 1,
            $signedUp->eventId(),
            $signedUp->eventId(),
            new \DateTimeImmutable('now'),
            [],
            $signedUp->primaryEmailVerificationCode(),
            $signedUp->primaryEmail(),
            'This is your verification code: https://example.com/page/?verificationCode='.$signedUp->primaryEmailVerificationCode(),
            'system.development-application.example.com',
            'Your Verification Code',
        );

        $this->getSQLEventStore()->beginTransaction();
        $actualPrimaryEmailVerificationCodeSent = $this->getSQLEventStore()->findEvent($expectedPrimaryEmailVerificationCodeSent->eventId()->id());
        $this->getSQLEventStore()->completeTransaction();

        Assert::assertEquals(
            [
                $expectedPrimaryEmailVerificationCodeSent->eventId(),
                $expectedPrimaryEmailVerificationCodeSent->aggregateId(),
                $expectedPrimaryEmailVerificationCodeSent->aggregateVersion(),
                $expectedPrimaryEmailVerificationCodeSent->correlationId(),
                $expectedPrimaryEmailVerificationCodeSent->causationId(),
                $expectedPrimaryEmailVerificationCodeSent->metadata(),
                $expectedPrimaryEmailVerificationCodeSent->verificationCodeSent(),
                $expectedPrimaryEmailVerificationCodeSent->toEmailAddress(),
                $expectedPrimaryEmailVerificationCodeSent->emailContents(),
                $expectedPrimaryEmailVerificationCodeSent->fromEmailAddress(),
                $expectedPrimaryEmailVerificationCodeSent->subjectLine(),
            ],
            [
                $actualPrimaryEmailVerificationCodeSent->eventId(),
                $actualPrimaryEmailVerificationCodeSent->aggregateId(),
                $actualPrimaryEmailVerificationCodeSent->aggregateVersion(),
                $actualPrimaryEmailVerificationCodeSent->correlationId(),
                $actualPrimaryEmailVerificationCodeSent->causationId(),
                $actualPrimaryEmailVerificationCodeSent->metadata(),
                $actualPrimaryEmailVerificationCodeSent->verificationCodeSent(),
                $actualPrimaryEmailVerificationCodeSent->toEmailAddress(),
                $actualPrimaryEmailVerificationCodeSent->emailContents(),
                $actualPrimaryEmailVerificationCodeSent->fromEmailAddress(),
                $actualPrimaryEmailVerificationCodeSent->subjectLine(),
            ],
        );

        Assert::assertInstanceOf(\DateTimeImmutable::class, $actualPrimaryEmailVerificationCodeSent->recordedOn());
    }

    public function testReactToEmailChangeRequested(): void
    {
        /** @var SendPrimaryEmailVerificationReactor $sendPrimaryEmailVerificationReactor */
        $sendPrimaryEmailVerificationReactor = $this->getContainer()
            ->get(SendPrimaryEmailVerificationReactor::class)
        ;

        $signedUp = SampleEvents::signedUp();
        $primaryEmailChangeRequested = SampleEvents::primaryEmailChangeRequested(
            $signedUp->aggregateId(),
            $signedUp->aggregateVersion() + 1,
            $signedUp->eventId(),
            $signedUp->eventId(),
        );
        $this->getSQLEventStore()->beginTransaction();
        $this->getSQLEventStore()->save($signedUp);
        $this->getSQLEventStore()->save($primaryEmailChangeRequested);
        $this->getSQLEventStore()->completeTransaction();
        $sendPrimaryEmailVerificationReactor->react($primaryEmailChangeRequested);

        $expectedPrimaryEmailVerificationCodeSent = PrimaryEmailVerificationCodeSent::new(
            Id::createNewByHashing('Identity/User/PrimaryEmailVerificationSent:'.$primaryEmailChangeRequested->eventId()->id()),
            $signedUp->aggregateId(),
            $primaryEmailChangeRequested->aggregateVersion() + 1,
            $primaryEmailChangeRequested->eventId(),
            $signedUp->eventId(),
            new \DateTimeImmutable('now'),
            [],
            $primaryEmailChangeRequested->newVerificationCode(),
            $primaryEmailChangeRequested->newEmailRequested(),
            'This is your verification code: https://example.com/page/?verificationCode='.$primaryEmailChangeRequested->newVerificationCode(),
            'system.development-application.example.com',
            'Your Verification Code',
        );

        $this->getSQLEventStore()->beginTransaction();
        $actualPrimaryEmailVerificationCodeSent = $this->getSQLEventStore()->findEvent($expectedPrimaryEmailVerificationCodeSent->eventId()->id());
        $this->getSQLEventStore()->completeTransaction();

        Assert::assertEquals(
            [
                $expectedPrimaryEmailVerificationCodeSent->eventId(),
                $expectedPrimaryEmailVerificationCodeSent->aggregateId(),
                $expectedPrimaryEmailVerificationCodeSent->aggregateVersion(),
                $expectedPrimaryEmailVerificationCodeSent->correlationId(),
                $expectedPrimaryEmailVerificationCodeSent->causationId(),
                $expectedPrimaryEmailVerificationCodeSent->metadata(),
                $expectedPrimaryEmailVerificationCodeSent->verificationCodeSent(),
                $expectedPrimaryEmailVerificationCodeSent->toEmailAddress(),
                $expectedPrimaryEmailVerificationCodeSent->emailContents(),
                $expectedPrimaryEmailVerificationCodeSent->fromEmailAddress(),
                $expectedPrimaryEmailVerificationCodeSent->subjectLine(),
            ],
            [
                $actualPrimaryEmailVerificationCodeSent->eventId(),
                $actualPrimaryEmailVerificationCodeSent->aggregateId(),
                $actualPrimaryEmailVerificationCodeSent->aggregateVersion(),
                $actualPrimaryEmailVerificationCodeSent->correlationId(),
                $actualPrimaryEmailVerificationCodeSent->causationId(),
                $actualPrimaryEmailVerificationCodeSent->metadata(),
                $actualPrimaryEmailVerificationCodeSent->verificationCodeSent(),
                $actualPrimaryEmailVerificationCodeSent->toEmailAddress(),
                $actualPrimaryEmailVerificationCodeSent->emailContents(),
                $actualPrimaryEmailVerificationCodeSent->fromEmailAddress(),
                $actualPrimaryEmailVerificationCodeSent->subjectLine(),
            ],
        );

        Assert::assertInstanceOf(\DateTimeImmutable::class, $actualPrimaryEmailVerificationCodeSent->recordedOn());
    }
}
