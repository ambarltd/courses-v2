<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration;

use Galeas\Api\Kernel;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;

abstract class KernelTestBase extends TestCase
{
    private $kernel;

    private $container;

    public function setUp(): void
    {
        if (null !== $this->kernel) {
            $this->kernel->boot();
            $this->container = $this->containerFromKernel($this->kernel);

            return;
        }

        parent::setUp();

        require __DIR__.'/../../vendor/autoload.php';

        // debug = true, because running a test should refresh the cache
        // this is only run once, so it shouldn't impact CI time
        $this->kernel = new Kernel("test", true);
        $this->kernel->boot();
        $this->container = $this->containerFromKernel($this->kernel);
    }

    protected function getKernel(): Kernel {
        return $this->kernel;
    }

    private function containerFromKernel(Kernel $kernel): Container
    {
        $container = $kernel->getContainer();
        if ($container instanceof Container) {
            return $container;
        }

        throw new \RuntimeException();
    }

    public function tearDown(): void
    {
        parent::tearDown();

        $this->kernel->shutdown();

        $this->container->reset();
    }
}