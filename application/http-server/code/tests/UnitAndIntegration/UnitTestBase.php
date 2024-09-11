<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration;

use PHPUnit\Framework\TestCase;

/**
 * Unit tests, which should not touch other components. As always, there may be exceptions and they should
 * be carefully considered.
 */
abstract class UnitTestBase extends TestCase
{
    /**
     * @throws \RuntimeException
     */
    final public static function setUpBeforeClass(): void
    {
        $environment = getenv('API_ENVIRONMENT_TYPE');

        if ('environment_test' !== $environment) {
            throw new \RuntimeException('Cannot execute tests unless in test mode');
        }
    }
}
