<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Identity\TakenEmail\Aggregate;

use Galeas\Api\BoundedContext\Identity\TakenEmail\Aggregate\TakenEmail;
use Galeas\Api\Common\Id\Id;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTest;

class TakenEmailTest extends UnitTest
{
    public function testCreate(): void
    {
        $aggregateId = Id::createNew();
        $aggregateVersion = 1;
        $takenEmailInLowercase = 'test@example.com';
        $takenByUser = Id::createNew();

        $takenEmail = TakenEmail::fromProperties(
            $aggregateId,
            $aggregateVersion,
            $takenEmailInLowercase,
            $takenByUser
        );

        Assert::assertEquals($aggregateId, $takenEmail->aggregateId());
        Assert::assertEquals($aggregateVersion, $takenEmail->aggregateVersion());
        Assert::assertEquals($takenEmailInLowercase, $takenEmail->takenEmailInLowercase());
        Assert::assertEquals($takenByUser, $takenEmail->takenByUser());
    }
}
