<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Identity\User\Event;

use Galeas\Api\BoundedContext\Identity\User\Aggregate\User;
use Galeas\Api\BoundedContext\Identity\User\Event\PrimaryEmailVerificationCodeSent;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\AccountDetails;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\Email;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\HashedPassword;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\UnverifiedEmail;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\VerificationCode;
use Galeas\Api\Common\Id\Id;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class PrimaryEmailVerificationCodeSentTest extends UnitTestBase
{
    public function testCreate(): void
    {
        $eventId = Id::createNew();
        $aggregateId = Id::createNew();
        $causationId = $eventId;
        $correlationId = $eventId;
        $primaryEmailChangeRequested = PrimaryEmailVerificationCodeSent::new(
            $eventId,
            $aggregateId,
            1432,
            $causationId,
            $correlationId,
            new \DateTimeImmutable("2024-01-03 10:35:23"),
            ["metadataField" => "hello world 123"],
            'verificationCode91230',
            'sentTo@example.com',
            'YourCode:verificationCode91230',
            'sentFrom@example.com',
            "Verify Yourself"
        );

        Assert::assertEquals(
            [
                $eventId,
                $aggregateId,
                1432,
                $causationId,
                $correlationId,
                new \DateTimeImmutable("2024-01-03 10:35:23"),
                ["metadataField" => "hello world 123"],
                'verificationCode91230',
                'sentTo@example.com',
                'YourCode:verificationCode91230',
                'sentFrom@example.com',
                "Verify Yourself"
            ],
            [
                $primaryEmailChangeRequested->eventId(),
                $primaryEmailChangeRequested->aggregateId(),
                $primaryEmailChangeRequested->aggregateVersion(),
                $primaryEmailChangeRequested->causationId(),
                $primaryEmailChangeRequested->correlationId(),
                $primaryEmailChangeRequested->recordedOn(),
                $primaryEmailChangeRequested->metadata(),
                $primaryEmailChangeRequested->verificationCodeSent(),
                $primaryEmailChangeRequested->toEmailAddress(),
                $primaryEmailChangeRequested->emailContents(),
                $primaryEmailChangeRequested->fromEmailAddress(),
                $primaryEmailChangeRequested->subjectLine(),
            ]
        );
    }

    public function testTransform(): void
    {
        $aggregateId = Id::createNew();
        $primaryEmailChangeRequested = PrimaryEmailVerificationCodeSent::new(
            Id::createNew(),
            $aggregateId,
            1432,
            Id::createNew(),
            Id::createNew(),
            new \DateTimeImmutable("2024-01-03 10:35:23"),
            ["metadataField" => "hello world 123"],
            'verificationCode91230',
            'sentTo@example.com',
            'YourCode:verificationCode91230',
            'sentFrom@example.com',
            "Verify Yourself"

        );
        $user = User::fromProperties(
            $aggregateId,
            27,
            UnverifiedEmail::fromEmailAndVerificationCode(
                Email::fromEmail('verified@example.com'),
                VerificationCode::fromVerificationCode("verificationCode91230"),
            ),
            HashedPassword::fromHash('1234abcdef'),
            AccountDetails::fromDetails(
                'username',
                true
            )
        );
        $transformedUser = $primaryEmailChangeRequested->transformUser($user);

        Assert::assertEquals(
            $user,
            $transformedUser
        );
    }
}
