<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Security\Session\Event;

use Galeas\Api\BoundedContext\Security\Session\Event\SignedIn;
use Galeas\Api\BoundedContext\Security\Session\ValueObject\SessionDetails;
use Galeas\Api\Common\Id\Id;
use Galeas\Api\Primitive\PrimitiveValidation\Session\SessionTokenValidator;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class SignedInTest extends UnitTestBase
{
    /**
     * @test
     */
    public function testCreate(): void
    {
        $metadata = [1, 2, 3];
        $asUser = Id::createNew();
        $withUsername = 'test_username';
        $withEmail = 'test_email';
        $withHashedPassword = 'test_hashed_password';
        $byDeviceLabel = 'by_device_label';
        $withIp = '127.0.0.1';

        $signedIn = SignedIn::fromProperties(
            $metadata,
            $asUser,
            $withUsername,
            $withEmail,
            $withHashedPassword,
            $byDeviceLabel,
            $withIp
        );

        Assert::assertNotEquals($signedIn->aggregateId(), $signedIn->eventId());
        Assert::assertNotEquals($signedIn->aggregateId(), $signedIn->asUser());
        Assert::assertNotEquals($signedIn->asUser(), $signedIn->eventId());
        Assert::assertEquals(null, $signedIn->sourceEventId());

        Assert::assertEquals($metadata, $signedIn->eventMetadata());
        Assert::assertEquals($asUser, $signedIn->asUser());
        Assert::assertEquals($asUser, $signedIn->authorizerId());
        Assert::assertEquals($withUsername, $signedIn->withUsername());
        Assert::assertEquals($withEmail, $signedIn->withEmail());
        Assert::assertEquals($withHashedPassword, $signedIn->withHashedPassword());
        Assert::assertEquals($byDeviceLabel, $signedIn->byDeviceLabel());
        Assert::assertEquals($withIp, $signedIn->withIp());
        Assert::assertTrue(
            SessionTokenValidator::isValid(
                $signedIn->sessionTokenCreated()
            )
        );
    }

    /**
     * @test
     */
    public function testCreateAggregate(): void
    {
        $signedIn = SignedIn::fromProperties(
            [1, 2, 3],
            Id::createNew(),
            'test_username',
            'test_email',
            'test_hashed_password',
            'by_device_label',
            '127.0.0.1'
        );

        $session = $signedIn->createSession();

        Assert::assertEquals(
            $signedIn->aggregateId(),
            $session->id()
        );
        Assert::assertEquals(
            SessionDetails::fromProperties(
                $signedIn->asUser(),
                $signedIn->withUsername(),
                $signedIn->withEmail(),
                $signedIn->withHashedPassword(),
                $signedIn->byDeviceLabel(),
                $signedIn->withIp(),
                $signedIn->sessionTokenCreated()
            ),
            $session->sessionDetails()
        );
        Assert::assertEquals(
            null,
            $session->sessionIsSignedOut()
        );
    }
}
