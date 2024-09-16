<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Identity\User\Event;

use Galeas\Api\BoundedContext\Identity\User\Aggregate\User;
use Galeas\Api\BoundedContext\Identity\User\Event\SignedUp;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\AccountDetails;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\Email;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\HashedPassword;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\UnverifiedEmail;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\VerificationCode;
use Galeas\Api\Common\Id\Id;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class SignedUpTest extends UnitTestBase
{
    public function testCreate(): void
    {
        $eventId = Id::createNew();
        $aggregateId = Id::createNew();
        $causationId = $eventId;
        $correlationId = $eventId;
        $signedUp = SignedUp::new(
            $eventId,
            $aggregateId,
            1432,
            $causationId,
            $correlationId,
            new \DateTimeImmutable("2024-01-03 10:35:23"),
            ["metadataField" => "hello world 123"],
            'primaryEmail@example.com',
            'primaryEmailVerificationCode',
            'hashedPassword',
            'username',
            true
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
                'primaryEmail@example.com',
                'primaryEmailVerificationCode',
                'hashedPassword',
                'username',
                true
            ],
            [
                $signedUp->eventId(),
                $signedUp->aggregateId(),
                $signedUp->aggregateVersion(),
                $signedUp->causationId(),
                $signedUp->correlationId(),
                $signedUp->recordedOn(),
                $signedUp->metadata(),
                $signedUp->primaryEmail(),
                $signedUp->primaryEmailVerificationCode(),
                $signedUp->hashedPassword(),
                $signedUp->username(),
                $signedUp->termsOfUseAccepted(),
            ]
        );
    }

    public function testCreateAggregate(): void
    {
        $eventId = Id::createNew();
        $aggregateId = Id::createNew();
        $causationId = $eventId;
        $correlationId = $eventId;
        $signedUp = SignedUp::new(
            $eventId,
            $aggregateId,
            1432,
            $causationId,
            $correlationId,
            new \DateTimeImmutable("2024-01-03 10:35:23"),
            ["metadataField" => "hello world 123"],
            'primaryEmail@example.com',
            'primaryEmailVerificationCode',
            'hashedPassword',
            'username',
            false
        );
        $user = $signedUp->createUser();

        Assert::assertEquals(
            User::fromProperties(
                $signedUp->aggregateId(),
                $signedUp->aggregateVersion(),
                UnverifiedEmail::fromEmailAndVerificationCode(
                    Email::fromEmail($signedUp->primaryEmail()),
                    VerificationCode::fromVerificationCode($signedUp->primaryEmailVerificationCode())
                ),
                HashedPassword::fromHash($signedUp->hashedPassword()),
                AccountDetails::fromDetails(
                    $signedUp->username(),
                    $signedUp->termsOfUseAccepted()
                )
            ),
            $user
        );
    }
}
