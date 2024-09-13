<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\Service\Queue;

use Galeas\Api\BoundedContext\Identity\User\Event\SignedUp;
use Galeas\Api\Service\Queue\InMemoryQueue;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Email\ValidEmails;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Security\ValidPasswords;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Username\ValidUsernames;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class InMemoryQueueTest extends UnitTestBase
{
    private function createNewInMemoryQueue(): InMemoryQueue
    {
        return new InMemoryQueue();
    }

    /**
     * @test
     */
    public function testEnqueueing(): void
    {
        $inMemoryQueue = $this->createNewInMemoryQueue();

        $signedUp = SignedUp::fromProperties(
            [],
            ValidEmails::listValidEmails()[0],
            ValidPasswords::listValidPasswords()[0],
            ValidUsernames::listValidUsernames()[0],
            true
        );
        $inMemoryQueue->enqueue($signedUp);

        /** @var SignedUp $storedEvent */
        $storedEvent = $inMemoryQueue->queuedEvents()[0];
        Assert::assertEquals(
            ValidEmails::listValidEmails()[0],
            $storedEvent->primaryEmail()
        );
        Assert::assertInternalType(
            'string',
            $storedEvent->primaryEmailVerificationCode()
        );
        Assert::assertTrue(
            password_verify(
                ValidPasswords::listValidPasswords()[0],
                $storedEvent->hashedPassword()
            )
        );
        Assert::assertEquals(
            ValidUsernames::listValidUsernames()[0],
            $storedEvent->username()
        );
        Assert::assertEquals(
            true,
            $storedEvent->termsOfUseAccepted()
        );
    }
}
