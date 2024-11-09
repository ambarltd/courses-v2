<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Identity\TakenEmail\Event;

use Galeas\Api\BoundedContext\Identity\TakenEmail\Aggregate\TakenEmail;
use Galeas\Api\BoundedContext\Identity\TakenEmail\Event\EmailTaken;
use Galeas\Api\Common\Id\Id;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTest;

class EmailTakenTest extends UnitTest
{
    public function testCreate(): void
    {
        $eventId = Id::createNew();
        $aggregateId = Id::createNew();
        $causationId = Id::createNew();
        $correlationId = Id::createNew();
        $takenByUser = Id::createNew();
        $emailTaken = EmailTaken::new(
            $eventId,
            $aggregateId,
            1,
            $causationId,
            $correlationId,
            new \DateTimeImmutable('2024-01-03 10:35:23'),
            ['metadataField' => 'hello world 123'],
            'test@example.com',
            $takenByUser
        );

        Assert::assertEquals(
            [
                $eventId,
                $aggregateId,
                1,
                $causationId,
                $correlationId,
                new \DateTimeImmutable('2024-01-03 10:35:23'),
                ['metadataField' => 'hello world 123'],
                'test@example.com',
                $takenByUser,
            ],
            [
                $emailTaken->eventId(),
                $emailTaken->aggregateId(),
                $emailTaken->aggregateVersion(),
                $emailTaken->causationId(),
                $emailTaken->correlationId(),
                $emailTaken->recordedOn(),
                $emailTaken->metadata(),
                $emailTaken->takenEmailInLowercase(),
                $emailTaken->takenByUser(),
            ]
        );
    }

    public function testCreateAggregate(): void
    {
        $emailTaken = EmailTaken::new(
            Id::createNew(),
            Id::createNew(),
            1,
            Id::createNew(),
            Id::createNew(),
            new \DateTimeImmutable('2024-01-03 10:35:23'),
            ['metadataField' => 'hello world 123'],
            'test@example.com',
            Id::createNew()
        );
        $takenEmail = $emailTaken->createTakenEmail();

        Assert::assertEquals(
            TakenEmail::fromProperties(
                $emailTaken->aggregateId(),
                $emailTaken->aggregateVersion(),
                $emailTaken->takenEmailInLowercase(),
                $emailTaken->takenByUser()
            ),
            $takenEmail
        );
    }
}
