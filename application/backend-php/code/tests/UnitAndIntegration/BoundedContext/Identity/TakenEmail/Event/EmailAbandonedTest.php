<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Identity\TakenEmail\Event;

use Galeas\Api\BoundedContext\Identity\TakenEmail\Aggregate\TakenEmail;
use Galeas\Api\BoundedContext\Identity\TakenEmail\Event\EmailAbandoned;
use Galeas\Api\Common\Id\Id;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTest;

class EmailAbandonedTest extends UnitTest
{
    public function testCreate(): void
    {
        $eventId = Id::createNew();
        $aggregateId = Id::createNew();
        $causationId = Id::createNew();
        $correlationId = Id::createNew();
        $emailAbandoned = EmailAbandoned::new(
            $eventId,
            $aggregateId,
            1,
            $causationId,
            $correlationId,
            new \DateTimeImmutable('2024-01-03 10:35:23'),
            ['metadataField' => 'hello world 123'],
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
            ],
            [
                $emailAbandoned->eventId(),
                $emailAbandoned->aggregateId(),
                $emailAbandoned->aggregateVersion(),
                $emailAbandoned->causationId(),
                $emailAbandoned->correlationId(),
                $emailAbandoned->recordedOn(),
                $emailAbandoned->metadata(),
            ]
        );
    }

    public function testTransformAbandoned(): void
    {
        $aggregateId = Id::createNew();
        $emailAbandoned = EmailAbandoned::new(
            Id::createNew(),
            $aggregateId,
            2,
            Id::createNew(),
            Id::createNew(),
            new \DateTimeImmutable(),
            ['metadataField' => 'hello world 123'],
        );
        $transformedTakenEmail = $emailAbandoned->transformTakenEmail(TakenEmail::fromProperties(
            $aggregateId,
            1,
            'test@example.com',
            Id::createNew()
        ));

        Assert::assertEquals(
            TakenEmail::fromProperties(
                $aggregateId,
                2,
                'test@example.com',
                null
            ),
            $transformedTakenEmail
        );
    }
}
