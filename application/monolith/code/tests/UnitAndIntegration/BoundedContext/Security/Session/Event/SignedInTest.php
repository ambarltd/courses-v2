<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Security\Session\Event;

use Galeas\Api\BoundedContext\Security\Session\Event\SignedIn;
use Galeas\Api\BoundedContext\Security\Session\ValueObject\SessionDetails;
use Galeas\Api\Common\Id\Id;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class SignedInTest extends UnitTestBase
{
    /**
     * @test
     */
    public function testCreate(): void
    {
        $eventId = Id::createNew();
        $aggregateId = Id::createNew();
        $causationId = $eventId;
        $correlationId = $eventId;
        $userId = Id::createNew();
        $signedIn = SignedIn::new(
            $eventId,
            $aggregateId,
            1432,
            $causationId,
            $correlationId,
            new \DateTimeImmutable("2024-01-03 10:35:23"),
            ["metadataField" => "hello world 123"],
            $userId,
            'UserNameBob',
            null,
            'HashedPassword819',
            'DeviceIphoneFrom2023',
            "201.201.20.201",
            "SessionTokenCreatedForSessionblablabla909090"
        );

        Assert::assertEquals(
            
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
