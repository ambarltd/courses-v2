<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Identity\TakenEmail\Event;

use Galeas\Api\BoundedContext\Identity\TakenEmail\Aggregate\TakenEmail;
use Galeas\Api\BoundedContext\Identity\TakenEmail\Event\AbandonedEmailRetaken;
use Galeas\Api\Common\Id\Id;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTest;

class AbandonedEmailRetakenTest extends UnitTest
{
    public function testCreate(): void
    {
        $eventId = Id::createNew();
        $aggregateId = Id::createNew();
        $causationId = Id::createNew();
        $correlationId = Id::createNew();
        $retakenByUser = Id::createNew();
        $abandonedEmailRetaken = AbandonedEmailRetaken::new(
            $eventId,
            $aggregateId,
            1,
            $causationId,
            $correlationId,
            new \DateTimeImmutable('2024-01-03 10:35:23'),
            ['metadataField' => 'hello world 123'],
            $retakenByUser
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
                $retakenByUser,
            ],
            [
                $abandonedEmailRetaken->eventId(),
                $abandonedEmailRetaken->aggregateId(),
                $abandonedEmailRetaken->aggregateVersion(),
                $abandonedEmailRetaken->causationId(),
                $abandonedEmailRetaken->correlationId(),
                $abandonedEmailRetaken->recordedOn(),
                $abandonedEmailRetaken->metadata(),
                $abandonedEmailRetaken->retakenByUser(),
            ]
        );
    }

    public function testTransformRetaken(): void
    {
        $abandonedEmailRetaken = AbandonedEmailRetaken::new(
            Id::createNew(),
            Id::createNew(),
            3,
            Id::createNew(),
            Id::createNew(),
            new \DateTimeImmutable(),
            ['metadataField' => 'hello world 123'],
            Id::createNew()
        );
        $transformedTakenEmail = $abandonedEmailRetaken->transformTakenEmail(TakenEmail::fromProperties(
            $abandonedEmailRetaken->aggregateId(),
            2,
            'test@example.com',
            null
        ));

        Assert::assertEquals(
            TakenEmail::fromProperties(
                $abandonedEmailRetaken->aggregateId(),
                3,
                'test@example.com',
                $abandonedEmailRetaken->retakenByUser()
            ),
            $transformedTakenEmail
        );
    }
}
