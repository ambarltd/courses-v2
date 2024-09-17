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

/**
 * The functions below are used in tests to avoid having to generate events over and over again.
 * It's recommended to not edit existing functions, because tests might make assumptions
 * using the results. At best, editing means you might break existing tests. And at worst, it means
 * that now you aren't testing certain things.
 *
 * There is a naming convention here, that should help us get different events for different use cases.
 * When we speak about another value (e.g., one more email) in the same aggregate, we refer to the new value
 * as "second" or "third" or "fourth" etc.
 * When we speak about another aggregate, or another value inside a separate aggregate, we prefix with "another".
 * Example:
 *  UserAggregate: SignIn with "email", Request change to a "second email".
 *  AnotherUserAggregate: SignIn with "another email", Request a change to "another second email".
 *
 * When there are tests you need to write that don't quite fit the sample events, don't try to pollute this class.
 * Instead, simply instantiate events directly in your test, the world won't end because of a few extra lines of code.
 */
abstract class SampleEvents {
    public static function userEvents(): array {
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
    
    public static function signedUp(): SignedUp {
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

    public static function primaryEmailVerificationCodeSent(
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
            "Your code is: " . self::sampleVerificationCode(),
            self::systemEmailFrom(),
            "Verify Yourself"
        );
    }

    public static function primaryEmailVerified(
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

    public static function primaryEmailChangeRequested(
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
            self::secondSampleEmail(),
            self::secondSampleVerificationCode(),
            self::sampleHashedPassword()
        );
    }

    public static function anotherSignedUp(): SignedUp {
        $eventId = Id::createNew();
        $aggregateId = Id::createNew();
        return SignedUp::new(
            $eventId,
            $aggregateId,
            1,
            $eventId,
            $eventId,
            new \DateTimeImmutable("now"),
            self::anotherSampleMetadata(null),
            self::anotherSampleEmail(),
            self::anotherSampleVerificationCode(),
            self::anotherSampleHashedPassword(),
            self::anotherSampleUsername(),
            true,
        );
    }


    public static function sessionEvents(): array {
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

    public static function signedIn(): SignedIn {
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

    public static function tokenRefreshed(
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
            self::secondSampleIp(),
            $existingSessionToken,
            self::secondSampleSessionToken()
        );
    }

    public static function signedOut(
        Id $aggregateId,
        int $aggregateVersion,
        Id $causationId,
        Id $correlationId
    ): SignedOut {
        $eventId = Id::createNew();
        $existingSessionToken = self::secondSampleSessionToken();
        return SignedOut::new(
            $eventId,
            $aggregateId,
            $aggregateVersion,
            $causationId,
            $correlationId,
            new \DateTimeImmutable("now"),
            self::sampleMetadata($existingSessionToken),
            self::thirdSampleIp(),
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

    private static function anotherSampleMetadata(?string $withSessionToken): array
    {
        return [
            "environment" => "browser",
            "devicePlatform" => "windows",
            "deviceModel" => "The OG",
            "deviceOSVersion" => "Windows 99.9999",
            "deviceOrientation" => "portrait",
            "latitude" => 15.3232,
            "longitude" => -25.32123,
            "ipAddress" => "150.102.12.3",
            "userAgent" => "A_COOL_USER_AGENT",
            "referer" => "2.example.com",
            "withSessionToken" => $withSessionToken,
        ];
    }

    private static function sampleEmail(): string {
        return "test@galeas.com";
    }

    private static function secondSampleEmail(): string {
        return "proof@galeas2.net";
    }

    private static function anotherSampleEmail(): string {
        return "anotherEmail@gmail.com";
    }

    private static function systemEmailFrom(): string {
        return "from@system.example.com";
    }

    private static function sampleHashedPassword(): string {
        return '$2y$10$tS8Y8CvwOeBVaFzPkXOfBuSearouW45pb5OlujqV6Y2BQPgvU5W2q'; // corresponds to "abcDEFg1/2"
    }

    private static function anotherSampleHashedPassword(): string {
        return '$2a$10$/q4ZluKn5QrNz2FizyFxaOtinBAfninfZTFAI/02d2kfHTcgTc336'; // corresponds to "b3rdsnn128FU&d9"
    }

    private static function sampleUsername(): string {
        return "MyUsername";
    }

    private static function anotherSampleUsername(): string {
        return "ThisIsMe";
    }

    private static function sampleVerificationCode(): string {
        return "FirstVerificationCode";
    }

    private static function secondSampleVerificationCode(): string {
        return "SecondVerificationCode";
    }

    private static function anotherSampleVerificationCode(): string {
        return "FirstVerificationCode";
    }

    private static function sampleDeviceLabel(): string {
        return "My Iphone Device Label";
    }

    private static function sampleIp(): string {
        return "130.130.130.130";
    }

    private static function secondSampleIp(): string {
        return "131.131.131.131";
    }

    private static function thirdSampleIp(): string {
        return "132.132.132.132";
    }

    private static function sampleSessionToken(): string {
        return "SessionToken17891028561029";
    }

    private static function secondSampleSessionToken(): string {
        return "SessionToken02067776337012";
    }
}