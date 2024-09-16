<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Security\Session\Event;

use Galeas\Api\BoundedContext\Security\Session\Aggregate\Session;
use Galeas\Api\BoundedContext\Security\Session\Event\SignedIn;
use Galeas\Api\BoundedContext\Security\Session\Event\SignedOut;
use Galeas\Api\BoundedContext\Security\Session\Event\TokenRefreshed;
use Galeas\Api\BoundedContext\Security\Session\ValueObject\SessionDetails;
use Galeas\Api\BoundedContext\Security\Session\ValueObject\SessionIsSignedOut;
use Galeas\Api\Common\Id\Id;
use Galeas\Api\Primitive\PrimitiveValidation\Session\SessionTokenValidator;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class SignedOutTest extends UnitTestBase
{
    public function testCreate(): void
    {
        $eventId = Id::createNew();
        $aggregateId = Id::createNew();
        $causationId = $eventId;
        $correlationId = $eventId;
        $signedOut = SignedOut::new(
            $eventId,
            $aggregateId,
            1,
            $causationId,
            $correlationId,
            new \DateTimeImmutable("2024-01-03 10:35:23"),
            ["metadataField" => "hello world 123"],
            "201.201.20.201",
            'existingSessionToken',
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
                "201.201.20.201",
                'existingSessionToken',
            ],
            [
                $signedOut->eventId(),
                $signedOut->aggregateId(),
                $signedOut->aggregateVersion(),
                $signedOut->causationId(),
                $signedOut->correlationId(),
                $signedOut->recordedOn(),
                $signedOut->metadata(),
                $signedOut->withIp(),
                $signedOut->withSessionToken(),
            ]
        );
    }

    public function testTransformAggregate(): void
    {
        $session = Session::fromProperties(
            Id::createNew(),
            1,
            SessionDetails::fromProperties(
                Id::createNew(),
                'test_username',
                'test_email',
                'test_hashed_password',
                'by_device_label',
                '127.0.0.1',
                'old_session_token'
            ),
            null
        );

        $eventId = Id::createNew();
        $aggregateId = Id::createNew();
        $causationId = $eventId;
        $correlationId = $eventId;
        $signedOut = SignedOut::new(
            $eventId,
            $aggregateId,
            2,
            $causationId,
            $correlationId,
            new \DateTimeImmutable("2024-01-03 10:35:23"),
            ["metadataField" => "hello world 123"],
            "201.201.20.201",
            'new_session_token',
        );

        $transformedSession = $signedOut->transformSession($session);

        Assert::assertEquals(
            Session::fromProperties(
                $session->aggregateId(),
                2,
                $session->sessionDetails(),
                SessionIsSignedOut::fromProperties(
                    $signedOut->withSessionToken(),
                    $signedOut->withIp()
                )
            ),
            $transformedSession
        );
    }
}
