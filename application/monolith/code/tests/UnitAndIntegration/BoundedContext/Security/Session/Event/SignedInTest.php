<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Security\Session\Event;

use Galeas\Api\BoundedContext\Security\Session\Aggregate\Session;
use Galeas\Api\BoundedContext\Security\Session\Event\SignedIn;
use Galeas\Api\BoundedContext\Security\Session\ValueObject\SessionDetails;
use Galeas\Api\Common\Id\Id;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class SignedInTest extends UnitTestBase
{
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
            1,
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
            [
                $eventId,
                $aggregateId,
                1,
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
            ],
            [
                $signedIn->eventId(),
                $signedIn->aggregateId(),
                $signedIn->aggregateVersion(),
                $signedIn->causationId(),
                $signedIn->correlationId(),
                $signedIn->recordedOn(),
                $signedIn->metadata(),
                $signedIn->asUser(),
                $signedIn->withUsername(),
                $signedIn->withEmail(),
                $signedIn->withHashedPassword(),
                $signedIn->byDeviceLabel(),
                $signedIn->withIp(),
                $signedIn->sessionTokenCreated(),
            ]
        );
    }

    public function testCreateAggregate(): void
    {
        $eventId = Id::createNew();
        $aggregateId = Id::createNew();
        $causationId = $eventId;
        $correlationId = $eventId;
        $userId = Id::createNew();
        $signedIn = SignedIn::new(
            $eventId,
            $aggregateId,
            1,
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
            $signedIn->createSession(),
            Session::fromProperties(
                $signedIn->aggregateId(),
                $signedIn->aggregateVersion(),
                SessionDetails::fromProperties(
                    $signedIn->asUser(),
                    $signedIn->withUsername(),
                    $signedIn->withEmail(),
                    $signedIn->withHashedPassword(),
                    $signedIn->byDeviceLabel(),
                    $signedIn->withIp(),
                    $signedIn->sessionTokenCreated(),
                ),
                null
            )
        );
    }
}
