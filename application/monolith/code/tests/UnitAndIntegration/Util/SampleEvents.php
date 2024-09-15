<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\Util;

use Galeas\Api\BoundedContext\Identity\User\Event\SignedUp;
use Galeas\Api\Common\Id\Id;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Email\ValidEmails;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Security\ValidPasswords;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Username\ValidUsernames;

abstract class SampleEvents {
    public static function signedUp(): SignedUp {
        $eventId = Id::createNew();
        return SignedUp::new(
            $eventId,
            Id::createNew(),
            1,
            $eventId,
            $eventId,
            new \DateTimeImmutable("now"),
            self::sampleMetadata(null),
            self::validEmail(),
            self::validPassword(),
            self::validUsername(),
            true
        );
    }

    private static function sampleMetadata(?string $withSessionToken) {
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

    private static function validEmail(): string {
        return "test@galeas.com";
    }

    private static function validPassword(): string {
        return "abcDEFg1/2";
    }

    private static function validUsername(): string {
        return "GaleasPerson";
    }
}