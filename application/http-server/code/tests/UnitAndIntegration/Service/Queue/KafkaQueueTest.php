<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\Service\Queue;

use Galeas\Api\BoundedContext\Identity\User\Event\SignedUp;
use Galeas\Api\Service\Queue\KafkaQueue;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\KernelTestBase;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Email\ValidEmails;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Security\ValidPasswords;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Username\ValidUsernames;

class KafkaQueueTest extends KernelTestBase
{
    /**
     * This is only testing the connection is not throwing exceptions.
     *
     * @test
     */
    public function testEnqueueing(): void
    {
        $kafkaQueue = $this->getContainer()->get(KafkaQueue::class);

        $signedUp = SignedUp::fromProperties(
            [],
            ValidEmails::listValidEmails()[0],
            ValidPasswords::listValidPasswords()[0],
            ValidUsernames::listValidUsernames()[0],
            true
        );
        $kafkaQueue->enqueue($signedUp);

        Assert::assertTrue(true); // prevents flagging as risky test
    }
}
