<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Identity\User\Event;

use Galeas\Api\BoundedContext\Identity\User\Aggregate\User;
use Galeas\Api\BoundedContext\Identity\User\Event\PrimaryEmailChangeRequested;
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

class PrimaryEmailChangeRequestedTest extends UnitTestBase
{
    public function testCreate(): void
    {
        $eventId = Id::createNew();
        $aggregateId = Id::createNew();
        $causationId = $eventId;
        $correlationId = $eventId;
        $primaryEmailChangeRequested = PrimaryEmailChangeRequested::new(
            $eventId,
            $aggregateId,
            1432,
            $causationId,
            $correlationId,
            new \DateTimeImmutable("2024-01-03 10:35:23"),
            ["metadataField" => "hello world 123"],
            'newEmailRequested@example.com',
            'newVerificationCode123',
            'hashedPassword123123',
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
                'newEmailRequested@example.com',
                'newVerificationCode123',
                'hashedPassword123123'
            ],
            [
                $primaryEmailChangeRequested->eventId(),
                $primaryEmailChangeRequested->aggregateId(),
                $primaryEmailChangeRequested->aggregateVersion(),
                $primaryEmailChangeRequested->causationId(),
                $primaryEmailChangeRequested->correlationId(),
                $primaryEmailChangeRequested->recordedOn(),
                $primaryEmailChangeRequested->metadata(),
                $primaryEmailChangeRequested->newEmailRequested(),
                $primaryEmailChangeRequested->newVerificationCode(),
                $primaryEmailChangeRequested->requestedWithHashedPassword(),
            ]
        );
    }

    public function testTransformVerified(): void
    {
        $aggregateId = Id::createNew();
        $primaryEmailChangeRequested = PrimaryEmailChangeRequested::new(
            Id::createNew(),
            $aggregateId,
            28,
            Id::createNew(),
            Id::createNew(),
            new \DateTimeImmutable(),
            ["metadataKey" => "123"],
            'newEmailRequested@example.com',
            'newVerificationCode123',
            'hashedPassword123123',
        );
        $user = User::fromProperties(
            $aggregateId,
            27,
            VerifiedEmail::fromEmail(
                Email::fromEmail('verified@example.com'),
            ),
            HashedPassword::fromHash('1234abcdef'),
            AccountDetails::fromDetails(
                'username',
                true
            )
        );
        $transformedUser = $primaryEmailChangeRequested->transformUser($user);

        Assert::assertEquals(
            User::fromProperties(
                $user->aggregateId(),
                28,
                VerifiedButRequestedNewEmail::fromEmailsAndVerificationCode(
                    Email::fromEmail('verified@example.com'),
                    Email::fromEmail('newEmailRequested@example.com'),
                    VerificationCode::fromVerificationCode('newVerificationCode123')
                ),
                $user->hashedPassword(),
                $user->accountDetails()
            ),
            $transformedUser
        );
    }

    public function testTransformChangeRequested(): void
    {
        $aggregateId = Id::createNew();
        $primaryEmailChangeRequested = PrimaryEmailChangeRequested::new(
            Id::createNew(),
            $aggregateId,
            28,
            Id::createNew(),
            Id::createNew(),
            new \DateTimeImmutable(),
            ["metadataKey" => "123"],
            'newEmailRequested@example.com',
            'newVerificationCode123',
            'hashedPassword123123',
        );
        $user = User::fromProperties(
            $aggregateId,
            27,
            VerifiedButRequestedNewEmail::fromEmailsAndVerificationCode(
                Email::fromEmail('verified@example.com'),
                Email::fromEmail('requested@example.com'),
                VerificationCode::fromVerificationCode('some_verification_code')
            ),
            HashedPassword::fromHash('1234abcdef'),
            AccountDetails::fromDetails(
                'username',
                true
            )
        );
        $transformedUser = $primaryEmailChangeRequested->transformUser($user);

        Assert::assertEquals(
            User::fromProperties(
                $user->aggregateId(),
                28,
                VerifiedButRequestedNewEmail::fromEmailsAndVerificationCode(
                    Email::fromEmail('verified@example.com'),
                    Email::fromEmail('newEmailRequested@example.com'),
                    VerificationCode::fromVerificationCode('newVerificationCode123')
                ),
                $user->hashedPassword(),
                $user->accountDetails()
            ),
            $transformedUser
        );
    }

    public function testTransformUnverified(): void
    {
        $aggregateId = Id::createNew();
        $primaryEmailChangeRequested = PrimaryEmailChangeRequested::new(
            Id::createNew(),
            $aggregateId,
            28,
            Id::createNew(),
            Id::createNew(),
            new \DateTimeImmutable(),
            ["metadataKey" => "123"],
            'newEmailRequested@example.com',
            'newVerificationCode123',
            'hashedPassword123123',
        );
        $user = User::fromProperties(
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
        );
        $transformedUser = $primaryEmailChangeRequested->transformUser($user);

        Assert::assertEquals(
            User::fromProperties(
                $user->aggregateId(),
                28,
                UnverifiedEmail::fromEmailAndVerificationCode(
                    Email::fromEmail('newEmailRequested@example.com'),
                    VerificationCode::fromVerificationCode('newVerificationCode123')
                ),
                $user->hashedPassword(),
                $user->accountDetails()
            ),
            $transformedUser
        );
    }
}
