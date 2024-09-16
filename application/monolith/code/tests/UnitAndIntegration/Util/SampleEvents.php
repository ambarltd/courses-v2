<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\Util;

use Galeas\Api\BoundedContext\Identity\User\Event\PrimaryEmailChangeRequested;
use Galeas\Api\BoundedContext\Identity\User\Event\PrimaryEmailVerificationCodeSent;
use Galeas\Api\BoundedContext\Identity\User\Event\PrimaryEmailVerified;
use Galeas\Api\BoundedContext\Identity\User\Event\SignedUp;
use Galeas\Api\BoundedContext\Security\Session\Event\SignedIn;
use Galeas\Api\BoundedContext\Security\Session\Event\SignedOut;
use Galeas\Api\BoundedContext\Security\Session\Event\TokenRefreshed;
use Galeas\Api\Common\Id\Id;
use Galeas\Api\Primitive\PrimitiveTransformation\Hash\BCryptPasswordHash;

abstract class SampleEvents {
    public static function sampleUserEvents(): array {
        $event1 = SampleEvents::signedUp();
        $event2 = SampleEvents::primaryEmailVerificationCodeSent(
            $event1->aggregateId(),
            2,
            $event1->eventId(),
            $event1->eventId()
        );
        $event3 = SampleEvents::primaryEmailVerified(
            $event1->aggregateId(),
            3,
            $event2->eventId(),
            $event1->eventId(),
        );
        $event4 = SampleEvents::primaryEmailChangeRequested(
            $event1->aggregateId(),
            4,
            $event3->eventId(),
            $event1->eventId()
        );
        
        return [$event1, $event2, $event3, $event4];
    }
    
    private static function signedUp(): SignedUp {
        $eventId = Id::createNew();
        $aggregateId = Id::createNew();
        return SignedUp::new(
            $eventId,
            $aggregateId,
            1,
            $eventId,
            $eventId,
            new \DateTimeImmutable("now"),
            self::sampleMetadata(null),
            self::sampleEmail(),
            self::sampleVerificationCode(),
            self::sampleHashedPassword(),
            self::sampleUsername(),
            true,
        );
    }

    private static function primaryEmailVerificationCodeSent(
        Id $aggregateId,
        int $aggregateVersion,
        Id $causationId,
        Id $correlationId,
    ): PrimaryEmailVerificationCodeSent {
        $eventId = Id::createNew();
        return PrimaryEmailVerificationCodeSent::new(
            $eventId,
            $aggregateId,
            $aggregateVersion,
            $causationId,
            $correlationId,
            new \DateTimeImmutable("now"),
            self::sampleMetadata(null),
            self::sampleVerificationCode(),
            self::sampleEmail(),
            "Your code is: " . self::sampleVerificationCode()
        );
    }

    private static function primaryEmailVerified(
        Id $aggregateId,
        int $aggregateVersion,
        Id $causationId,
        Id $correlationId,
    ): PrimaryEmailVerified {
        $eventId = Id::createNew();
        return PrimaryEmailVerified::new(
            $eventId,
            $aggregateId,
            $aggregateVersion,
            $causationId,
            $correlationId,
            new \DateTimeImmutable("now"),
            self::sampleMetadata(null),
            self::sampleVerificationCode(),
        );
    }

    private static function primaryEmailChangeRequested(
        Id $aggregateId,
        int $aggregateVersion,
        Id $causationId,
        Id $correlationId
    ): PrimaryEmailChangeRequested {
        $eventId = Id::createNew();
        return PrimaryEmailChangeRequested::new(
            $eventId,
            $aggregateId,
            $aggregateVersion,
            $causationId,
            $correlationId,
            new \DateTimeImmutable("now"),
            self::sampleMetadata(null),
            self::anotherSampleEmail(),
            self::anotherSampleVerificationCode(),
            self::sampleHashedPassword()
        );
    }


    public static function sampleSessionEvents(): array {
        $event1 = SampleEvents::signedIn();
        $event2 = SampleEvents::tokenRefreshed(
            $event1->aggregateId(),
            2,
            $event1->eventId(),
            $event1->eventId()
        );
        $event3 = SampleEvents::signedOut(
            $event1->aggregateId(),
            3,
            $event2->eventId(),
            $event1->eventId()
        );

        return [$event1, $event2, $event3];
    }

    private static function signedIn(): SignedIn {
        $eventId = Id::createNew();
        $aggregateId = Id::createNew();
        $asUser = Id::createNew();
        return SignedIn::new(
            $eventId,
            $aggregateId,
            1,
            $eventId,
            $eventId,
            new \DateTimeImmutable("now"),
            self::sampleMetadata(null),
            $asUser,
            null,
            self::sampleEmail(),
            self::sampleHashedPassword(),
            self::sampleDeviceLabel(),
            self::sampleIp(),
            self::sampleSessionToken()
        );
    }

    private static function tokenRefreshed(
        Id $aggregateId,
        int $aggregateVersion,
        Id $causationId,
        Id $correlationId
    ): TokenRefreshed {
        $eventId = Id::createNew();
        $existingSessionToken = self::sampleSessionToken();
        return TokenRefreshed::new(
            $eventId,
            $aggregateId,
            $aggregateVersion,
            $causationId,
            $correlationId,
            new \DateTimeImmutable("now"),
            self::sampleMetadata($existingSessionToken),
            self::anotherSampleIp(),
            $existingSessionToken,
            self::anotherSampleSessionToken()
        );
    }

    private static function signedOut(
        Id $aggregateId,
        int $aggregateVersion,
        Id $causationId,
        Id $correlationId
    ): SignedOut {
        $eventId = Id::createNew();
        $existingSessionToken = self::anotherSampleSessionToken();
        return SignedOut::new(
            $eventId,
            $aggregateId,
            $aggregateVersion,
            $causationId,
            $correlationId,
            new \DateTimeImmutable("now"),
            self::sampleMetadata($existingSessionToken),
            self::yetAnotherSampleIp(),
            $existingSessionToken,
        );
    }

    private static function sampleMetadata(?string $withSessionToken): array
    {
        return [
            "environment" => "native",
            "devicePlatform" => "linux",
            "deviceModel" => "Penguin 1.0",
            "deviceOSVersion" => "Ubuntu 14.04",
            "deviceOrientation" => "landscape",
            "latitude" => 12.32123,
            "longitude" => 22.32123,
            "ipAddress" => "120.123.193.12",
            "userAgent" => "Test_UserAgent",
            "referer" => "example.com",
            "withSessionToken" => $withSessionToken,
        ];
    }

    private static function sampleEmail(): string {
        return "test@galeas.com";
    }

    private static function anotherSampleEmail(): string {
        return "proof@galeas2.net";
    }

    private static function sampleHashedPassword(): string {
        return '$2y$10$tS8Y8CvwOeBVaFzPkXOfBuSearouW45pb5OlujqV6Y2BQPgvU5W2q'; // corresponds to "abcDEFg1/2"
    }

    private static function sampleUsername(): string {
        return "MyUsername";
    }

    private static function sampleVerificationCode(): string {
        return "FirstVerificationCode";
    }

    private static function anotherSampleVerificationCode(): string {
        return "SecondVerificationCode";
    }

    private static function sampleDeviceLabel(): string {
        return "My Iphone Device Label";
    }

    private static function sampleIp(): string {
        return "130.130.130.130";
    }

    private static function anotherSampleIp(): string {
        return "131.131.131.131";
    }

    private static function yetAnotherSampleIp(): string {
        return "132.132.132.132";
    }

    private static function sampleSessionToken(): string {
        return "SessionToken17891028561029";
    }

    private static function anotherSampleSessionToken(): string {
        return "SessionToken02067776337012";
    }
}