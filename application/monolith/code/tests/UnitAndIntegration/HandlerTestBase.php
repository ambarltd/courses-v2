<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration;

use Galeas\Api\Service\EventStore\InMemoryEventStore;
use Galeas\Api\Service\Queue\InMemoryQueue;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Handler tests are integration tests in the sense that they check how handlers interact
 * with their dependencies (event store, queues, and others).
 * They are unit tests in the sense that they do not interact with real databases and queues;
 * instead in memory abstractions are used, which speeds up the tests, and leaves the
 * full integration to be covered by end to end tests.
 */
abstract class HandlerTestBase extends TestCase
{
    /**
     * @var InMemoryEventStore
     */
    private $inMemoryEventStore;


    protected function getInMemoryEventStore(): InMemoryEventStore
    {
        return $this->inMemoryEventStore;
    }

    /**
     * @param mixed $methodWillReturnValue
     */
    protected function mockForCommandHandlerWithReturnValue(
        string $mockedInterfaceName,
        string $mockedMethodName,
        $methodWillReturnValue
    ): MockObject {
        $mock = $this->createMock($mockedInterfaceName);
        $mock->method($mockedMethodName)
            ->willReturn($methodWillReturnValue);

        return $mock;
    }

    protected function mockForCommandHandlerWithCallback(
        string $mockedInterfaceName,
        string $mockedMethodName,
        callable $callback
    ): MockObject {
        $mock = $this->createMock($mockedInterfaceName);
        $mock->method($mockedMethodName)
            ->willReturnCallback($callback);

        return $mock;
    }

    protected function mockMetadata(): array
    {
        return [];
    }

    private function clearEventStore(): void
    {
        $this->inMemoryEventStore = new InMemoryEventStore();
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->clearEventStore();
    }

    public function tearDown(): void
    {
        parent::tearDown();

        $this->clearEventStore();
    }
}
