<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Security\Session\Event;

use Galeas\Api\BoundedContext\Security\Session\Aggregate\Session;
use Galeas\Api\BoundedContext\Security\Session\Event\SignedIn;
use Galeas\Api\BoundedContext\Security\Session\Event\TokenRefreshed;
use Galeas\Api\BoundedContext\Security\Session\ValueObject\SessionDetails;
use Galeas\Api\Common\Id\Id;
use Galeas\Api\Primitive\PrimitiveValidation\Session\SessionTokenValidator;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class TokenRefreshedTest extends UnitTestBase
{
    public function testCreate(): void
    {
        $eventId = Id::createNew();
        $aggregateId = Id::createNew();
        $causationId = $eventId;
        $correlationId = $eventId;
        $tokenRefreshed = TokenRefreshed::new(
            $eventId,
            $aggregateId,
            1,
            $causationId,
            $correlationId,
            new \DateTimeImmutable("2024-01-03 10:35:23"),
            ["metadataField" => "hello world 123"],
            "201.201.20.201",
            'existingSessionToken',
            'refreshedSessionToken',
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
                'refreshedSessionToken',
            ],
            [
                $tokenRefreshed->eventId(),
                $tokenRefreshed->aggregateId(),
                $tokenRefreshed->aggregateVersion(),
                $tokenRefreshed->causationId(),
                $tokenRefreshed->correlationId(),
                $tokenRefreshed->recordedOn(),
                $tokenRefreshed->metadata(),
                $tokenRefreshed->withIp(),
                $tokenRefreshed->withExistingSessionToken(),
                $tokenRefreshed->refreshedSessionToken(),
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
                'with_session_token'
            ),
            null
        );

        $eventId = Id::createNew();
        $aggregateId = Id::createNew();
        $causationId = $eventId;
        $correlationId = $eventId;
        $tokenRefreshed = TokenRefreshed::new(
            $eventId,
            $aggregateId,
            2,
            $causationId,
            $correlationId,
            new \DateTimeImmutable("2024-01-03 10:35:23"),
            ["metadataField" => "hello world 123"],
            "201.201.20.201",
            'existingSessionToken',
            'refreshedSessionToken',
        );

        $transformedSession = $tokenRefreshed->transformSession($session);

        Assert::assertEquals(
            Session::fromProperties(
                $session->aggregateId(),
                2,
                SessionDetails::fromProperties(
                    $session->sessionDetails()->asUser(),
                    $session->sessionDetails()->withUsername(),
                    $session->sessionDetails()->withEmail(),
                    $session->sessionDetails()->withHashedPassword(),
                    $session->sessionDetails()->byDeviceLabel(),
                    $tokenRefreshed->withIp(),
                    $tokenRefreshed->refreshedSessionToken()
                ),
                $session->sessionIsSignedOut()
            ),
            $transformedSession
        );
    }
}
