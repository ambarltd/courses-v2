<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Identity\User\Event;

use Galeas\Api\BoundedContext\Identity\User\Aggregate\User;
use Galeas\Api\BoundedContext\Identity\User\Event\PrimaryEmailVerified;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\AccountDetails;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\Email;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\HashedPassword;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\VerifiedButRequestedNewEmail;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\UnverifiedEmail;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\VerificationCode;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\VerifiedEmail;
use Galeas\Api\Common\Id\Id;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class PrimaryEmailVerifiedTest extends UnitTestBase
{
    public function testCreate(): void
    {
        $eventId = Id::createNew();
        $aggregateId = Id::createNew();
        $causationId = $eventId;
        $correlationId = $eventId;
        $primaryEmailVerified = PrimaryEmailVerified::new(
            $eventId,
            $aggregateId,
            1432,
            $causationId,
            $correlationId,
            new \DateTimeImmutable("2024-01-03 10:35:23"),
            ["metadataField" => "hello world 123"],
            'verifiedWithCode',
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
                'verifiedWithCode'
            ],
            [
                $primaryEmailVerified->eventId(),
                $primaryEmailVerified->aggregateId(),
                $primaryEmailVerified->aggregateVersion(),
                $primaryEmailVerified->causationId(),
                $primaryEmailVerified->correlationId(),
                $primaryEmailVerified->recordedOn(),
                $primaryEmailVerified->metadata(),
                $primaryEmailVerified->verifiedWithCode(),
            ]
        );
    }

    public function testTransformUnverified(): void
    {
        $aggregateId = Id::createNew();
        $primaryEmailVerified = PrimaryEmailVerified::new(
            Id::createNew(),
            $aggregateId,
            28,
            Id::createNew(),
            Id::createNew(),
            new \DateTimeImmutable(),
            ["metadataKey" => "123"],
            'changed_code_1234'
        );
        $transformedUser = $primaryEmailVerified->transformUser(User::fromProperties(
            $aggregateId,
            27,
            UnverifiedEmail::fromEmailAndVerificationCode(
                Email::fromEmail('test@example.com'),
                VerificationCode::fromVerificationCode('some_verification_code')
            ),
            HashedPassword::fromHash('1234abcdef'),
            AccountDetails::fromDetails(
                'username',
                true
            )
        ));

        Assert::assertEquals(
            User::fromProperties(
                $aggregateId,
                28,
                VerifiedEmail::fromEmail(
                    Email::fromEmail("test@example.com"),
                ),
                HashedPassword::fromHash("1234abcdef"),
                AccountDetails::fromDetails(
                    "username",
                    true
                )
            ),
            $transformedUser
        );
    }

    public function testTransformVerified(): void
    {
        $aggregateId = Id::createNew();
        $primaryEmailVerified = PrimaryEmailVerified::new(
            Id::createNew(),
            $aggregateId,
            28,
            Id::createNew(),
            Id::createNew(),
            new \DateTimeImmutable(),
            ["metadataKey" => "123"],
            'changed_code_1234'
        );
        $transformedUser = $primaryEmailVerified->transformUser(User::fromProperties(
            $aggregateId,
            27,
            VerifiedEmail::fromEmail(
                Email::fromEmail("previous@example.com"),
            ),
            HashedPassword::fromHash('1234abcdef'),
            AccountDetails::fromDetails(
                'username',
                true
            )
        ));

        Assert::assertEquals(
            User::fromProperties(
                $aggregateId,
                28,
                VerifiedEmail::fromEmail(
                    Email::fromEmail("previous@example.com"),
                ),
                HashedPassword::fromHash("1234abcdef"),
                AccountDetails::fromDetails(
                    "username",
                    true
                )
            ),
            $transformedUser
        );
    }

    public function testTransformRequestedChange(): void
    {
        $aggregateId = Id::createNew();
        $primaryEmailVerified = PrimaryEmailVerified::new(
            Id::createNew(),
            $aggregateId,
            28,
            Id::createNew(),
            Id::createNew(),
            new \DateTimeImmutable(),
            ["metadataKey" => "123"],
            'changed_code_1234'
        );
        $transformedUser = $primaryEmailVerified->transformUser(User::fromProperties(
            $aggregateId,
            27,
            VerifiedButRequestedNewEmail::fromEmailsAndVerificationCode(
                Email::fromEmail(
                    'test@example.com'
                ),
                Email::fromEmail(
                    'requested@example.com'
                ),
                VerificationCode::fromVerificationCode(
                    'codeFortest@example.com'
                )
            ),
            HashedPassword::fromHash('1234abcdef'),
            AccountDetails::fromDetails(
                'username',
                true
            )
        ));

        Assert::assertEquals(
            User::fromProperties(
                $aggregateId,
                28,
                VerifiedEmail::fromEmail(
                    Email::fromEmail("requested@example.com"),
                ),
                HashedPassword::fromHash("1234abcdef"),
                AccountDetails::fromDetails(
                    "username",
                    true
                )
            ),
            $transformedUser
        );
    }
}
