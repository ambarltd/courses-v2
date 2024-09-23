<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration;

use Doctrine\DBAL\Connection;
use Doctrine\ODM\MongoDB\DocumentManager;
use Galeas\Api\Kernel;
use Galeas\Api\Service\EventStore\SQLEventStoreConnection;
use Galeas\Api\Service\ODM\DocumentManagerForTests;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


// todo??? make the event store pass any saved events to all projection handlers?
abstract class IntegrationTest extends TestCase
{

    /**
     * @var Kernel
     */
    private $kernel;

    /**
     * @var Container
     */
    private $container;

    public function setUp(): void
    {
        if (null !== $this->kernel && null !== $this->container) {
            $this->kernel->boot();
            $this->container = $this->containerFromKernel($this->kernel);

            return;
        }

        parent::setUp();

        require __DIR__.'/../../vendor/autoload.php';

        // debug = true, because running a test should refresh the cache
        // this is only run once, so it shouldn't impact CI time
        $this->kernel = new Kernel("test", false);
        $this->kernel->boot();
        $this->container = $this->containerFromKernel($this->kernel);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        $this->kernel->shutdown();
        $this->container->reset();
    }

    protected function getKernel(): Kernel
    {
        return $this->kernel;
    }

    protected function getContainer(): Container
    {
        return $this->container;
    }

    private function containerFromKernel(Kernel $kernel): Container
    {
        $container = $kernel->getContainer();
        if ($container instanceof Container) {
            return $container;
        }

        throw new \RuntimeException();
    }
}